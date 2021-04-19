<?php

namespace tcCore\Http\Controllers;

use Illuminate\Http\Request;
use tcCore\Http\Controllers\Controller;

class RttiImportController extends Controller {

    /**
     *
     * @var type
     */
    protected $dateStamp;

    /**
     *
     * @var type
     */
    protected $basePath;

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request) {

        \set_time_limit(3 * 60);

        try {

            $this->validate($request, [
                'csv_file' => 'required',
                'separator' => 'required',
                'email_domain' => 'required',
            ]);

            $this->requestData = $request->all();

            $email_domain = $this->requestData['email_domain'];
            $separator = $this->requestData['separator'];

            $file = $request->file('csv_file');

            $fileName = $file->getClientOriginalName();
            $this->basePath = storage_path('app/rtti_import');

            $startDir = $this->dateStamp = date('YmdHis');

            $file->move(sprintf('%s/%s', $this->basePath, $startDir), $fileName);

            $file_path = $this->basePath . '/' . $startDir . '/' . $fileName;

            if (!file_exists($file_path)) {
                return ['errors' => ["file does not exists"], 'report' => 'path ' . $this->csv_file_path];
            }

            $rtti_import_helper = new \tcCore\Http\Helpers\RTTIImportHelper($file_path, $email_domain);

            $rtti_import_helper->validateEmailDomain($email_domain);

            $rtti_import_helper->getDataFromFile($file_path, $separator);

            $rtti_import_helper->csv_file_name = $file;

            if (count($rtti_import_helper->csv_data) <= 1) {
                throw new \Exception('No data in CSV ');
            } else {
                $rtti_import_helper->importLog('Data read from file');
            }

            $validation_report = $rtti_import_helper->validate();

            If (count($validation_report['errors']) > 0) {
                $rtti_import_helper->importLog('Validation errors ' . $validation_report['errors'][0]);
                // give feedback
                throw new \Exception('Data validation errors in CSV <br>' . implode('<br>', $validation_report['errors']));
            } else {
                $rtti_import_helper->importLog('No validation errors');
            }

            $return = $rtti_import_helper->process();

            if ($return['errors'] == []) {

                return response()->json(['error' => $return['data']], 200);
            } else {

                $error_message = 'Versie 0.1 We hebben helaas een aantal fouten geconstateerd waardoor we de import niet goed '
                        . 'konden afronden<br />Je kunt hiervoor contact opnemen met de Teach & Learn Company en daarbij '
                        . 'als referentie <br><br>rtti_import/' . $startDir . ' mee geven<br /><br>' . $return['errors'];

                return response()->json(['error' => $error_message], 200);
            }
        } catch (\Exception $e) {

            if (isset($return['data'])) {

                return response()->json(['error' => $return['data']], 200);
            } else {

                $error_message = 'Versie 0.1 We hebben helaas een aantal fouten geconstateerd waardoor we de import niet goed '
                        . 'konden afronden<br />Je kunt hiervoor contact opnemen met de Teach & Learn Company en daarbij '
                        . 'als referentie <br><br>rtti_import/' . $startDir . ' mee geven<br /><br>' . $e->getMessage();

                return response()->json(['error' => $error_message], 200);
            }

            exit;
        }
    }

    protected function getRequestData($fields) {
        if (is_string($fields)) {
            if (array_key_exists($fields, $this->requestData)) {
                return $this->requestData[$fields];
            }
            return '';
        }

        $fillableData = [];
        foreach ($fields as $field) {
            if (array_key_exists($field, $this->requestData)) {
                $fillableData[$field] = $this->getRequestData($field);
            }
        }
        return $fillableData;
    }

}
