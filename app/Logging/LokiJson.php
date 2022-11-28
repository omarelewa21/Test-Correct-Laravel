<?php declare(strict_types=1);

/*
 * This file is part of the Monolog package.
 *
 * (c) Jordi Boggiano <j.boggiano@seld.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace tcCore\Logging;

use Monolog\Formatter\NormalizerFormatter;

use Throwable;

/**
 * Encodes whatever record data is passed to it as json
 *
 * This can be useful to log to databases or remote APIs
 *
 * @author Jordi Boggiano <j.boggiano@seld.be>
 *
 * @phpstan-import-type Record from \Monolog\Logger
 */
class LokiJson extends NormalizerFormatter
{
    public const BATCH_MODE_JSON = 1;
    public const BATCH_MODE_NEWLINES = 2;

    /** @var self::BATCH_MODE_* */
    protected $batchMode;
    /** @var bool */
    protected $appendNewline;
    /** @var bool */
    protected $ignoreEmptyContextAndExtra;
    /** @var bool */
    protected $includeStacktraces = false;

    /**
     * @param self::BATCH_MODE_* $batchMode
     */
    public function __construct(int $batchMode = self::BATCH_MODE_JSON, bool $appendNewline = true, bool $ignoreEmptyContextAndExtra = false)
    {
        $this->batchMode = $batchMode;
        $this->appendNewline = $appendNewline;
        $this->ignoreEmptyContextAndExtra = $ignoreEmptyContextAndExtra;

        parent::__construct();
    }

    /**
     * The batch mode option configures the formatting style for
     * multiple records. By default, multiple records will be
     * formatted as a JSON-encoded array. However, for
     * compatibility with some API endpoints, alternative styles
     * are available.
     */
    public function getBatchMode(): int
    {
        return $this->batchMode;
    }

    /**
     * True if newlines are appended to every formatted record
     */
    public function isAppendingNewlines(): bool
    {
        return $this->appendNewline;
    }

    /**
     * {@inheritDoc}
     * @noinspection UnsupportedStringOffsetOperationsInspection
     */
    public function format(array $record): string
    {
        $normalized = $this->normalize($record);

        // Add default context

        //Request was from Cake
        if (request()->ip() == config('cake_laravel_filter.remote_addr')) {
            $normalized['ip'] = request()->header('cakeRealIP',request()->ip());

            if (request()->header('cakeUrlPath', null)) {
                $normalized['cake_url_path'] = request()->header('cakeUrlPath', null);
            }
        } else {
            $normalized['ip'] = request()->ip();
        }
        
        $normalized['request_path'] = request()->path();
        $normalized['request_method'] = request()->method();
        $normalized['action'] = class_basename(request()->route()->getAction()['controller']);
        
        # Store GET parameters in logs
        $normalized = array_merge($normalized, array_map(
                    function($v) {try { return $v->getKey();
                } catch (\Throwable $th) {
                    return $v;
                }
            }, request()->route()->parameters()));
        if (request()->user() != null) {
            $normalized['current_user_id'] = request()->user()->id;
            foreach (request()->user()->roles as $key => $value) {
                $normalized['current_role'][] = $value['name'];
            }
        }

        // put context one level higher for Loki to index
        foreach ($normalized['context'] as $key => $value) {
            if (!array_key_exists($key, $normalized)) {
                $normalized[$key] = $value;
                unset($normalized['context'][$key]);
            }
        }

        if ($normalized['context'] == []) {
            unset($normalized['context']);
        }
        if ($normalized['extra'] == []) {
            unset($normalized['extra']);
        }

        return $this->toJson($normalized, true) . ($this->appendNewline ? "\n" : '');
    }

    /**
     * {@inheritDoc}
     */
    public function formatBatch(array $records): string
    {
        switch ($this->batchMode) {
            case static::BATCH_MODE_NEWLINES:
                return $this->formatBatchNewlines($records);

            case static::BATCH_MODE_JSON:
            default:
                return $this->formatBatchJson($records);
        }
    }

    /**
     * @return void
     */
    public function includeStacktraces(bool $include = true)
    {
        $this->includeStacktraces = $include;
    }

    /**
     * Return a JSON-encoded array of records.
     *
     * @phpstan-param Record[] $records
     */
    protected function formatBatchJson(array $records): string
    {
        return $this->toJson($this->normalize($records), true);
    }

    /**
     * Use new lines to separate records instead of a
     * JSON-encoded array.
     *
     * @phpstan-param Record[] $records
     */
    protected function formatBatchNewlines(array $records): string
    {
        $instance = $this;

        $oldNewline = $this->appendNewline;
        $this->appendNewline = false;
        array_walk($records, function (&$value, $key) use ($instance) {
            $value = $instance->format($value);
        });
        $this->appendNewline = $oldNewline;

        return implode("\n", $records);
    }

    /**
     * Normalizes given $data.
     *
     * @param mixed $data
     *
     * @return mixed
     */
    protected function normalize($data, int $depth = 0)
    {
        if ($depth > $this->maxNormalizeDepth) {
            return 'Over '.$this->maxNormalizeDepth.' levels deep, aborting normalization';
        }

        if (is_array($data)) {
            $normalized = [];

            $count = 1;
            foreach ($data as $key => $value) {
                if ($count++ > $this->maxNormalizeItemCount) {
                    $normalized['...'] = 'Over '.$this->maxNormalizeItemCount.' items ('.count($data).' total), aborting normalization';
                    break;
                }

                $normalized[$key] = $this->normalize($value, $depth + 1);
            }

            return $normalized;
        }

        if ($data instanceof \DateTimeInterface) {
            return $this->formatDate($data);
        }

        if ($data instanceof Throwable) {
            return $this->normalizeException($data, $depth);
        }

        if (is_resource($data)) {
            return parent::normalize($data);
        }

        return $data;
    }

    /**
     * Normalizes given exception with or without its own stack trace based on
     * `includeStacktraces` property.
     *
     * {@inheritDoc}
     */
    protected function normalizeException(Throwable $e, int $depth = 0): array
    {
        $data = parent::normalizeException($e, $depth);
        if (!$this->includeStacktraces) {
            unset($data['trace']);
        }

        return $data;
    }
}