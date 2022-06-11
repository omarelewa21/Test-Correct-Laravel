<?php

namespace tcCore\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
use tcCore\UserInfosDontShow;
use DOMDocument;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class InfoController extends Controller
{
    private $key = '<I&*}":BLi/pa>O,/IrJN4w4k#>Qh@';

    public function index(IndexInfoRequest $request)
    {
        $data = null;
        switch($request->mode){
            case 'index':
                $data = Info::orderBy('show_from','desc')->with('roles')->get();
                break;
            case 'dashboard':
                $data = Info::getInfoForUser(Auth::user(), true);
                break;
            default:
                $data = Info::getInfoForUser(Auth::user());
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
        $info->saveRoleInfo($request->validated('roles'));
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
        $info->saveRoleInfo($request->validated('roles'));
        return Response::make($info,200);
    }

    public function delete(DeleteInfoRequest $request, Info $info)
    {
        $info->delete();
        return Response::make(true,200);
    }

    public function removeDashboardInfo(Info $info){
        if(!auth()->user()->isA('student')){
            logger($info);
            UserInfosDontShow::create([
                'user_id'       => auth()->id(),
                'info_id'       => $info->getKey()
            ]);
            return Response::make(true,200);
        }

        return Response::make(false, 500);

    }

    /**
     * Get image from cak part and replaces each img src with laravel src
     * @param (string) html content
     * @return (string) html content
     */
    public function handleInlineImage($content){
        $dom = new DOMDocument();
        $dom->loadHTML($content, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        $imgs = $dom->getElementsByTagName('img');

        foreach($imgs as $img) {
            $src = $img->getAttribute('src');
            $url_arr = parse_url($src);
            if($url_arr['host'] !== request()->getHost()){          // continue if saved image domain !=  test-correct.{env_type}
                parse_str($url_arr['query'], $query);
                $filename = $query['filename'];
                $response = Http::post(                                                                         // Send a post request to get image content
                    $url_arr['scheme'] . '://' . $url_arr['host'] . '/infos/inlineInfoImage/' . $filename,      // link
                    ['key' => $this->key]                                                                       // secret key between cake and laravel
                );
                if($response->successful()){
                    $storagePath = storage_path() . sprintf('/inlineimages/%f', $filename);
                    Storage::disk('inline_images')->put($filename, base64_decode($response->body()));
                    $img->setAttribute('src', request()->getSchemeAndHttpHost() . '/infos/inline-image/'. $filename);
                }
            }
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
        $image_path = storage_path('inlineimages/') . $image;
        if(file_exists($image_path)){
            return Response::file($image_path);
        }else{
            return Response::noContent();
        }
    }
}
