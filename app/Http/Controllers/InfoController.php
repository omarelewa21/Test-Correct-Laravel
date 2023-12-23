<?php

namespace tcCore\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use tcCore\Deployment;
use tcCore\Http\Requests\CreateDeploymentRequest;
use tcCore\Http\Requests\CreateInfoRequest;
use tcCore\Http\Requests\DeleteDeploymentRequest;
use tcCore\Http\Requests\DeleteInfoRequest;
use tcCore\Http\Requests\IndexDeploymentRequest;
use tcCore\Http\Requests\IndexInfoRequest;
use tcCore\Http\Requests\ShowDeploymentRequest;
use tcCore\Http\Requests\ShowInfoRequest;
use tcCore\Http\Requests\UpdateDeploymentRequest;
use tcCore\Http\Requests\UpdateInfoRequest;
use tcCore\Info;
use tcCore\Scopes\InfoBaseType;
use tcCore\UserInfosDontShow;
use DOMDocument;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use tcCore\UserSystemSetting;

class InfoController extends Controller
{
    private $key = '<I&*}":BLi/pa>O,/IrJN4w4k#>Qh@';

    public function index(IndexInfoRequest $request)
    {
        switch($request->mode){
            case 'index':
                $data = Info::orderBy('show_from','desc')->with('roles')->get();
                break;
            case 'dashboard':
                $data = Info::getForUser(Auth::user(), true);
                break;
            case 'feature':
                $data = Info::getForFeature();
                break;
            case 'types':
                $data = Info::getDisplayTypes();
                break;
            default:
                $data = Info::getForUser(Auth::user());
        }

        return Response::make($data, 200);
    }

    public function show(ShowInfoRequest $request, Info $info)
    {
        return Response::make($info->load('roles'), 200);
    }

    public function update(UpdateInfoRequest $request, Info $info)
    {
        $data = collect($request->validated());
        if(Str::contains($data->get('content_nl'), '<img')){
            $data = $data->replace([
                'content_nl' => $this->handleInlineImage($data->get('content_nl'))
            ]);
        }
        if(Str::contains($data->get('content_en'), '<img')){
            $data = $data->replace([
                'content_en' => $this->handleInlineImage($data->get('content_en'))
            ]);
        }
        $info->fill($data->all());
        $info->save();
        $info->saveRoleInfo($request->validated());
        return Response::make($info,200);
    }

    public function store(CreateInfoRequest $request)
    {
        $data = collect($request->validated());
        if(Str::contains($data->get('content_nl'), '<img')){
            $data = $data->replace([
                'content_nl' => $this->handleInlineImage($data->get('content_nl'))
            ]);
        }
        if(Str::contains($data->get('content_en'), '<img')){
            $data = $data->replace([
                'content_en' => $this->handleInlineImage($data->get('content_en'))
            ]);
        }
        $info = Info::create($data->all());
        $info->saveRoleInfo($request->validated());

        return Response::make($info,200);
    }

    public function delete(DeleteInfoRequest $request, Info $info)
    {
        $info->delete();
        return Response::make(true,200);
    }

    public function removeDashboardInfo(Info $info){
        if(!auth()->user()->isA('student')){
            UserInfosDontShow::create([
                'user_id'       => auth()->id(),
                'info_id'       => $info->getKey()
            ]);
            return Response::make(true,200);
        }

        return Response::make(false, 500);

    }

    public function seenNewFeatures(){

        $user = Auth::user();

        if(!auth()->user()->isA('Teacher')) {
            return Response::make(false, 401);
        }

        UserSystemSetting::setSetting($user,'newFeaturesSeen',Carbon::now()->timestamp);

        return Response::make(true,200);

    }

    public function closedNewFeaturesMessage(){

        $user = Auth::user();

        if(!auth()->user()->isA('Teacher')) {
            return Response::make(false, 401);
        }

        UserSystemSetting::setSetting($user,'closedNewFeaturesMessage',Carbon::now()->timestamp);

        return Response::make(true,200);

    }

    /**
     * Get image from cake part and replace each cake img src with laravel src
     * @param (string) html content
     * @return (string) html content
     */
    public function handleInlineImage($content){
        $dom = new DOMDocument();
        $dom->loadHTML($content, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        $imgs = $dom->getElementsByTagName('img');
        try {
            foreach($imgs as $img) {
                $src = $img->getAttribute('src');
                $url_arr = parse_url($src);

                if($url_arr['host'] !== request()->getHost()){                          // continue if saved image domain !=  test-correct.{env_type}
                    parse_str($url_arr['query'], $query);
                    $filename = $query['filename'];
                    if(Storage::disk('cake')->exists('questionanswers/' . $filename)){                                          // check image file exists in cake
                        $contents = Storage::disk('cake')->get('questionanswers/' . $filename);                                 // get image content

                        if(Storage::disk('inline_images')->put($filename, $contents)){                                          // if storing to laravel succeeds
                            Storage::disk('cake')->delete('questionanswers/' . $filename);                                      // delete file in cake
                            $img->setAttribute('src', config('app.base_url') . 'infos/inline-image/'. $filename);   // reference laravel path in img src
                        }
                    }
                }
            }
        } catch (\Exception $e) {
        }

        $html = $dom->saveHTML();
        return $html;
    }

    /**
     * Serve inline image links
     * @param string image name
     * @return response of image content
     */
    public function getInlineImage($image){
        if (Storage::disk('inline_images')->exists($image)) {
            $path = Storage::disk('inline_images')->path($image);
            return Response::file($path);
        } else {
            return Response::noContent();
        }
    }
}
