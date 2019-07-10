<?php namespace tcCore\Lib\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class MtiBuilder extends Builder
{

    /**
     * Get the hydrated models without eager loading.
     *
     * @param  array $columns
     * @return \Illuminate\Database\Eloquent\Model[]
     */
    public function getModels($columns = array('*'))
    {
        if ($this->model->useMti()) {
            // First, we will simply get the raw results from the query builders which we
            // can use to populate an array with Eloquent models. We will pass columns
            // that should be selected as well, which are typically just everything.
            $results = $this->query->get($columns);
            $connection = $this->model->getConnectionName();

            // Because of mti, we need to do some additional fetching
            $childTableData = array();
            $namespace = $this->getModelNamespace();

            $models = array();
            // Foreach result, identify what additional child row need to be fetched.
            foreach ($results as $result) {
                if (property_exists($result, $this->model->mtiClassField)) {
                    $class = $namespace . $result->{$this->model->mtiClassField};
                } else {
                    $class = get_class($this->model);
                }

                if (array_key_exists($class, $models)) {
                    $model = $models[$class];
                } else {
                    $model = $models[$class] = new $class();
                }

                if ($model->mtiBaseClass === get_class($this->model)) {
                    if ($model->getTable() !== $this->model->getTable()) {
                        $childTableData[$class][$result->{$this->model->getKeyName()}] = null;
                    }
                } else {
                    if (array_key_exists($this->model->mtiBaseClass, $models)) {
                        $parentModel = $models[$this->model->mtiBaseClass];
                    } else {
                        $parentModel = $models[$this->model->mtiBaseClass] = new $this->model->mtiBaseClass();
                    }

                    $childTableData[$this->model->mtiBaseClass][$result->{$parentModel->getKeyName()}] = null;
                }
            }

            // Foreach childtable, fetch the rows these results need.
            foreach ($childTableData as $class => $data) {
                $model = $models[$class];

                $childDataRows = DB::table($model->getTable())->whereIn($model->getKeyName(), array_keys($data))->get();
                foreach ($childDataRows as $childDataRow) {
                    $childTableData[$class][$childDataRow->{$model->getKeyName()}] = $childDataRow;
                }
            }

            $resultModels = array();
            // Once we have the results, we can spin through them and instantiate a fresh
            // model instance for each records we retrieved from the database. We will
            // also set the proper connection name for the model after we create it.
            foreach ($results as $result) {
                if (property_exists($result, $this->model->mtiClassField)) {
                    $class = $namespace . $result->{$this->model->mtiClassField};
                } else {
                    $class = get_class($this->model);
                }

                if (array_key_exists($class, $models)) {
                    $model = $models[$class];
                } else {
                    continue;
                }

                if (get_class($model) !== $model->mtiBaseClass) {
                    $parentModel = new $model->mtiBaseClass();

                    if ($model->mtiBaseClass !== get_class($this->model)) {
                        $parentData = $childTableData[$model->mtiBaseClass][$result->{$parentModel->getKeyName()}];
                        $parentModel = $parentModel->newFromBuilder($parentData);
                    } else {
                        $parentModel = $parentModel->newFromBuilder($result);
                    }

                    $parentModel->setConnection($connection);
                } else {
                    $parentModel = null;
                }

                if ($model->getTable() !== $this->model->getTable()) {
                    $childData = $childTableData[$class][$result->{$this->model->getKeyName()}];
                } else {
                    $childData = $result;
                }

                $resultModels[] = $childModel = $model->newFromBuilder($childData, $parentModel);
                $childModel->setConnection($connection);
            }

            return $resultModels;
        } else {
            return parent::getModels($columns);
        }
    }

    public function processColumns($columns)
    {
        $results = array();

        foreach ($columns as $column) {
            if (($column == '*' || strpos($column, '.') === false) && strpos($column, '(') === false) {
                $results[] = $this->model->getTable() . '.' . $column;
            } else {
                $results[] = $column;
            }
        }
        $parent = $this->model->mtiBaseClass;
        $parent = new $parent();
        $results[] = $parent->getTable() . '.' . $parent->mtiClassField;

        return $results;
    }

    /**
     * Get an array with the values of a given column.
     * old version later replaced by pluck due to migration to laravel 5.3.
     * TODO may be removed in later versions. Note was added at 10 Jul 2019
     * @param  string $column
     * @param  string $key
     * @return array
     */
    public function lists($column, $key = null)
    {
        $results = $this->query->pluck($column, $key);

        // If the model has a mutator for the requested column, we will spin through
        // the results and mutate the values so that the mutated version of these
        // columns are returned as you would expect from these Eloquent models.
        if ($this->model->hasGetMutator($column)) {
            foreach ($results as $key => &$value) {
                $fill = [$column => $value];

                $value = $this->model->newFromBuilder($fill)->$column;
            }
        }

        return collect($results);
    }

    /**
     * Get an array with the values of a given column.
     *
     * @param  string $column
     * @param  string $key
     * @return array
     */
    public function pluck($column, $key = null)
    {
        $results = $this->query->pluck($column, $key);

        // If the model has a mutator for the requested column, we will spin through
        // the results and mutate the values so that the mutated version of these
        // columns are returned as you would expect from these Eloquent models.
        if ($this->model->hasGetMutator($column)) {
            foreach ($results as $key => &$value) {
                $fill = [$column => $value];

                $value = $this->model->newFromBuilder($fill)->$column;
            }
        }

        return collect($results);
    }

    protected function getModelNamespace()
    {
        $slices = explode('\\', get_class($this->model));
        if (count($slices) > 1) {
            return implode('\\', array_slice($slices, 0, -1)) . '\\';
        } else {
            return '';
        }
    }
}