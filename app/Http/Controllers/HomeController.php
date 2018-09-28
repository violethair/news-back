<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Post;
use App\Category;
use App\Article;

class HomeController extends Controller
{
	public function index () {

		$lastest = Post::orderBy('id', 'desc')->limit(4);
		$lastest = $this->getPostFullInfo($lastest->get()->toArray());
		$pressReleases = Article::orderBy('id','desc')->limit(3)->get()->toArray();
		$analysis = Post::where('cat_id',3)->limit(7)->get()->toArray();
		$all = Post::orderBy('id', 'desc')->limit(12)->get()->toArray();
		$all = $this->getPostFullInfo($all);

		return response([
	        'status' => 'success',
	        'lastest' => $lastest,
	        'pressReleases' => $pressReleases,
	        'analysis' => $analysis,
	        'all' => $all
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
		$all = Post::orderBy('id', 'desc')->skip($page * 12)->take(12)->get()->toArray();
		$all = $this->getPostFullInfo($all);

		return response([
	        'status' => 'success',
	        'data' => $all
	       ], 200);
	}

	public function getPostInfo ($query) {

		$data = Post::where('link', $query)->first();

		if(empty($data)) return response(['status'=>'error', 'message'=>'Not found'],404);

		$data = $this->getPostFullInfo($data);

		$data['content'] = preg_replace('/(https:\/\/img.iholding.io)\/(.*)fill\!(.*)/', env('APP_URL') . '/postThumb/${3}', $data['content']);
		$data['content'] = preg_replace('/(http:\/\/img.iholding.io)\/(.*)fill\!(.*)/', env('APP_URL') . '/postThumb/${3}', $data['content']);

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
					$abcda = Post::select(['name','images','link','publish_at'])->where('id',$value)->first()->toArray();
					$temp[$count] = $abcda;
					$count++;
				}
				$result['relate'] = $temp;
			}
			return $result;
		}
		
	}
}
