<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Post;
use App\Category;
use App\Article;
use App\Setting;
use App\PressRelease;
use App\Video;

class HomeController extends Controller
{
	public function index () {

		$lastest = Post::where('status', 'publish')->orderBy('id', 'desc')->limit(4);
		$lastest = $this->getPostFullInfo($lastest->get()->toArray());
		$pressReleases = Article::orderBy('id','desc')->limit(3)->get()->toArray();
		$analysis = Post::where('status', 'publish')->where('cat_id',3)->orderBy('id', 'desc')->limit(7)->get()->toArray();
		$all = Post::where('status', 'publish')->orderBy('id', 'desc')->limit(12)->get()->toArray();
		$all = $this->getPostFullInfo($all);

		return response([
	        'status' => 'success',
	        'lastest' => $lastest,
	        'pressReleases' => $pressReleases,
	        'analysis' => $analysis,
	        'all' => $all
	       ], 200);
	}

	public function getSetting () {
		$data = Setting::first()->toArray();
		return response([
	        'status' => 'success',
	        'data' => $data
	       ], 200);
	}

	public function getCategory () {

		$data = Category::whereNull('parent_id')->orderBy('order')->get()->toArray();

		foreach($data as $key=>$value) {
			$data[$key]['child'] = Category::where('parent_id', $value['id'])->orderBy('order')->get()->toArray();
		}

		return response([
	        'status' => 'success',
	        'data' => $data,
	       ], 200);
	}

	public function getMorePost($page) {
		$all = Post::where('status', 'publish')->orderBy('id', 'desc')->skip($page * 12)->take(12)->get()->toArray();
		$all = $this->getPostFullInfo($all);

		return response([
	        'status' => 'success',
	        'data' => $all
	       ], 200);
	}

	public function getMorePostInCategory ($category_id,$page) {

		if($category_id == 12) {
			$all = PressRelease::orderBy('id', 'desc')->skip($page * 10)->take(10)->get()->toArray();
		} else {
			$all = Post::where('status', 'publish')->where('cat_id', $category_id)->orderBy('id', 'desc')->skip($page * 10)->take(10)->get()->toArray();
		}
		
		return response([
	        'status' => 'success',
	        'data' => $all
	       ], 200);
	}

	public function getPostInfo ($query) {

		$data = Post::where('status', 'publish')->where('link', $query)->first();

		if(empty($data)) {
			$data = PressRelease::where('link', $query)->first();
			if(empty($data)) return response(['status'=>'error', 'message'=>'Not found'],404);
		}

		// + view
		$data->view++;
		$data->save();

		$data = $this->getPostFullInfo($data);

		$data['content'] = preg_replace('/(https:\/\/img.iholding.io)\/(.*)fill\!(.*)/', env('APP_URL') . '/postThumb/${3}', $data['content']);
		$data['content'] = preg_replace('/(http:\/\/img.iholding.io)\/(.*)fill\!(.*)/', env('APP_URL') . '/postThumb/${3}', $data['content']);

		return response([
	        'status' => 'success',
	        'data' => $data
	       ], 200);
	}

	public function getCategoryInfo ($query) {

		if($query == 'press-releases') {
			$data['id'] = 12;
			$data['name'] = "Press Release";
			$data['link'] = 'press-releases';
			$data['post'] = PressRelease::orderBy('id','desc')->limit(10)->get()->toArray();
		} else {
			$data = Category::where('link', $query)->first();
			if(empty($data)) {
				return response(['status'=>'error', 'message'=>'Not found'],404);
			}
			$data = $data->toArray();
			$data['post'] = Post::where('cat_id', $data['id'])->orderBy('id','desc')->limit(10)->get()->toArray();
		}

		if(!empty($data['parent_id'])) {
			$data['parent'] = Category::find($data['parent_id']);
		}

		return response([
	        'status' => 'success',
	        'data' => $data
	       ], 200);
	}

	public function getTopNew () {
		$data = Post::where('view', '!=', 0)->orderBy('view', 'desc')->limit(5)->get()->toArray();
		return response([
	        'status' => 'success',
	        'data' => $data
	       ], 200);
	}

	public function uploadAvatar (Request $req) {
		if ($req->hasFile('image')) {
            $file = $req->image;
            $date = date('d-m-Y');
	        $path = storage_path() . '/app/public/post-thumb/' . $date;
	        if (!file_exists($path)) {
			    mkdir($path, 0777, true);
			}
			$avatarFileName = str_random(64) . '.' . $file->getClientOriginalExtension();
	        $file->move($path, $avatarFileName);
	        return response([
		        'status' => 'success',
		        'data' => 'iholding.io/' . $date . '/' . $avatarFileName
		       ], 200);
        } else {
        	return response([
	        	'status' => 'error',
	        	'data' => 'Please select file'
	      	], 500);
        }
	}

	public function getVideo () {
		$data = Video::orderBy('id', 'desc')->limit(20)->get()->toArray();
		return response([
	        'status' => 'success',
	        'data' => $data
	       ], 200);
	}

	public function getPressReleases () {
		$data = Article::orderBy('id', 'desc')->limit(5)->get()->toArray();
		return response([
	        'status' => 'success',
	        'data' => $data
	       ], 200);
	}

	// helper functions
	public function getPostFullInfo ($data) {
		if(is_array($data)) {
			foreach($data as $key=>$value) {
				$data[$key]['category'] = Category::find($value['cat_id'])->toArray();
			}
			return $data;
		} else {
			$result = $data;
			$result['category'] = Category::find($data['cat_id'])->toArray();
			if($data['relate_id'] != null) {
				$arr = explode(",",$data['relate_id']);
				$temp = [];
				$count=0;
				foreach($arr as $key=>$value) {
					if($key == 5) break;
					$abcda = Post::select(['name','images','link','shortdes','publish_at'])->where('id',$value)->first()->toArray();
					$temp[$count] = $abcda;
					$count++;
				}
				$result['relate'] = $temp;
			}
			return $result;
		}
		
	}
}
