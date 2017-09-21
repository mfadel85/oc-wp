<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
//use DB;u
use Illuminate\Support\Facades\DB;

class CategoryJson extends Controller
{
    function getCategories(){
    	$json = '';
    	$json = file_get_contents('https://www.elektroniksigaraevi.org/kargo/generate_json_catergories.php');
		$obj = json_decode($json);
		dd($obj);

    	//return $json;
    }
}
