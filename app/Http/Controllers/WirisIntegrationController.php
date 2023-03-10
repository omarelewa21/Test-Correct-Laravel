<?php

namespace tcCore\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class WirisIntegrationController extends Controller
{
    public function configurationjs() {
        /*include(public_path().'/ckeditor/plugins/ckeditor_wiris/integration/configurationjs.php');
        exit;*/
        // Above code DOES NOT WORK, due to Laravel interfering in Wiris' code
        // The string returned below is a result of running the integration directly then grabbing the output
        // This code should be considered a HACK, and you should ONLY use this in DEVELOPMENT ENVIRONMENTS where the PHP integration does not work (such as with Vale)
        // You can switch between the PHP integration and this hack by editing the CKEditor integration (config.js)
        //   and setting config.mathTypeParameters.serviceProviderProperties.server to either 'php' (the official integration) or 'java' (this hack)
        return Response::make('{"editorEnabled":true,"imageMathmlAttribute":"data-mathml","saveMode":"xml","base64savemode":"default","saveHandTraces":false,"parseModes":["latex"],"editorAttributes":"width=570, height=450, scroll=no, resizable=yes","editorUrl":"https://www.wiris.net/client/editor/editor","modalWindow":true,"modalWindowFullScreen":false,"CASEnabled":false,"CASMathmlAttribute":"alt","CASAttributes":"width=640, height=480, scroll=no, resizable=yes","hostPlatform":"unknown","versionPlatform":"unknown","enableAccessibility":true,"editorToolbar":"","chemEnabled":true,"imageFormat":"svg","editorParameters":{},"wirisPluginPerformance":true,"version":"7.26.0.1439"}', 200, array("content-type" => "application/json"));
    }

    public function showimage() {
        logger(__METHOD__ . __CLASS__ . __LINE__);
        include(public_path().'/ckeditor_png/plugins/ckeditor_wiris/integration/showimage.php');
    }

    public function createimage() {
        logger(__METHOD__ . __CLASS__ . __LINE__);
        include(public_path().'/ckeditor_png/plugins/ckeditor_wiris/integration/createimage.php');
    }
}
