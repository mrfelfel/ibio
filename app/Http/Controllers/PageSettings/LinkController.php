<?php

namespace App\Http\Controllers\PageSettings;

use App\Http\Controllers\Controller;
use App\Http\Requests\LinkStoreRequest;
use App\Http\Requests\LinkUpdateRequest;
use App\Http\Resources\LinkResource;
use App\Models\Link;
use App\Traits\UploadImage;
use Illuminate\Http\Request;

class LinkController extends Controller
{
    use UploadImage;

    /**
     * Display a listing of the resource.
     *
     * @return \Inertia\Response|\Inertia\ResponseFactory
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index()
    {
        $this->authorize('viewAny', Link::class);

        return inertia('page-settings/links/Index', [
            'title' => __('تنظیمات صفحه - لینک ها'),
            'menu' => 'page-settings',
            'subMenu' => 'links',
            'links' => LinkResource::collection(auth()->user()->pageLinks()->orderBy('order')->get())
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param LinkStoreRequest $request
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Exception
     */
    public function store(LinkStoreRequest $request)
    {
        $this->authorize('create', Link::class);

        try {
            $lastLink = $request->user()->links()->orderBy('order', 'desc')->first();
            $request->user()->links()->create(array_merge($request->validated(), [
                'order' => $lastLink ? $lastLink->order + 1 : 1
            ]));

            return redirect()->route('page-settings.links')->with([
                'success' => __('لینک اضافه شد')
            ]);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function sort(Request $request)
    {
        foreach ($request->links as $key => $link) {
            $link = $request->user()->links()->findOrFail($link['id']);
            $this->authorize('update', $link);
            $link->order = $key + 1;
            $link->save();
        }

        return back();
    }

    /**
     * @param Link $link
     * @return \Inertia\Response|\Inertia\ResponseFactory
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(Link $link)
    {
        $this->authorize('view', $link);

        return inertia('page-settings/links/Index', [
            'title' => __('تنظیمات صفحه - لینک ها'),
            'menu' => 'page-settings',
            'subMenu' => 'links',
            'link' => new LinkResource($link),
            'links' => LinkResource::collection(auth()->user()->pageLinks()->orderBy('order')->get())
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param LinkUpdateRequest $request
     * @param \App\Models\Link $link
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function update(LinkUpdateRequest $request, Link $link)
    {
        $this->authorize('update', $link);

        $link->update($request->validated());

        return back()->with([
            'success' => __('تغییرات ذخیره شد')
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\Link $link
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Exception
     */
    public function destroy(Link $link)
    {
        $this->authorize('delete', $link);

        $link->delete();

        return redirect()->route('page-settings.links')->with([
            'success' => __('لینک حذف شد')
        ]);
    }
}
