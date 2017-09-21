<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Automattic\WooCommerce\Client;

ini_set('max_execution_time', 1580);

class Importer extends Controller
{
	public function Importer(){
		/*$woo = new Client('http://elektroniksigarasi.org','ck_50cdc4f4e1f7fd654a478ebd2a2e7ca63c407eae',
    		'cs_399fe3ab9d66def517bf744c942fec2edf6b4a88',
	    [
	        'wp_api' => true,
	        'version' => 'wc/v2'
	    ]);*/
	}
    //
   public function parse_json($file){
    	$json = json_decode(file_get_contents($file),true);
    	if(is_array($json)&&!empty($json)):
    		return $json;
    	else :
    		die('An error occured while parsing '.$file.' file ');
    	endif;
    }
	function get_attributes_from_json( $json ) {
		$proudqtct_attributes = array();
		foreach( $json as $key => $pre_product ) :
			if ( !empty( $pre_product['attribute_name'] ) && !empty( $pre_product['attribute_value'] ) ) :
				$product_attributes[$pre_product['attribute_name']]['terms'][] = $pre_product['attribute_value'];
			endif;
		endforeach;		
	 
		return $product_attributes;
	 
	}
   
	function get_products_and_variations_from_json( $json, $added_attributes ) {
 
		$product = array();
		$product_variations = array();
	 
		foreach ( $json as $key => $pre_product ) :
	 
			if ( $pre_product['type'] == 'simple' ) :
				$product[$key]['_product_id'] = (string) $pre_product['product_id'];
	 
				$product[$key]['name'] = (string) $pre_product['name'];
				$product[$key]['description'] = (string) $pre_product['description'];
				$product[$key]['regular_price'] = (string) $pre_product['regular_price'];
	 
				// Stock
				$product[$key]['manage_stock'] = (bool) $pre_product['manage_stock'];
	 
				if ( $pre_product['stock'] > 0 ) :
					$product[$key]['in_stock'] = (bool) true;
					$product[$key]['stock_quantity'] = (int) $pre_product['stock'];
				else :
					$product[$key]['in_stock'] = (bool) false;
					$product[$key]['stock_quantity'] = (int) 0;
				endif;	
	 
			elseif ( $pre_product['type'] == 'variable' ) :
				$product[$key]['_product_id'] = (string) $pre_product['product_id'];
	 
				$product[$key]['type'] = 'variable';
				$product[$key]['name'] = (string) $pre_product['name'];
				$product[$key]['description'] = (string) $pre_product['description'];
				$product[$key]['regular_price'] = (string) $pre_product['regular_price'];
	 
				// Stock
				$product[$key]['manage_stock'] = (bool) $pre_product['manage_stock'];
	 
				if ( $pre_product['stock'] > 0 ) :
					$product[$key]['in_stock'] = (bool) true;
					$product[$key]['stock_quantity'] = (int) $pre_product['stock'];
				else :
					$product[$key]['in_stock'] = (bool) false;
					$product[$key]['stock_quantity'] = (int) 0;
				endif;	
	 
				$attribute_name = $pre_product['attribute_name'];
	 
				$product[$key]['attributes'][] = array(
						'id' => (int) $added_attributes[$attribute_name]['id'],
						'name' => (string) $attribute_name,
						'position' => (int) 0,
						'visible' => true,
						'variation' => true,
						'options' => $added_attributes[$attribute_name]['terms']
				);
	 
			elseif ( $pre_product['type'] == 'product_variation' ) :	
	 
				$product_variations[$key]['_parent_product_id'] = (string) $pre_product['parent_product_id'];
	 
				$product_variations[$key]['description'] = (string) $pre_product['description'];
				$product_variations[$key]['regular_price'] = (string) $pre_product['regular_price'];
	 
				// Stock
				$product_variations[$key]['manage_stock'] = (bool) $pre_product['manage_stock'];
	 
				if ( $pre_product['stock'] > 0 ) :
					$product_variations[$key]['in_stock'] = (bool) true;
					$product_variations[$key]['stock_quantity'] = (int) $pre_product['stock'];
				else :
					$product_variations[$key]['in_stock'] = (bool) false;
					$product_variations[$key]['stock_quantity'] = (int) 0;
				endif;
	 
				$attribute_name = $pre_product['attribute_name'];
				$attribute_value = $pre_product['attribute_value'];
	 
				$product_variations[$key]['attributes'][] = array(
					'id' => (int) $added_attributes[$attribute_name]['id'],
					'name' => (string) $attribute_name,
					'option' => (string) $attribute_value
				);
	 
			endif;		
		endforeach;		
	 
		$data['products'] = $product;
		$data['product_variations'] = $product_variations;
	 
		return $data;
	}
    
    function merge_products_and_variations( $product_data = array(), $product_variations_data = array() ) {
		foreach ( $product_data as $k => $product ) :
			foreach ( $product_variations_data as $k2 => $product_variation ) :
				if ( $product_variation['_parent_product_id'] == $product['_product_id'] ) :
	 
					// Unset merge key. Don't need it anymore
					unset($product_variation['_parent_product_id']);
	 
					$product_data[$k]['variations'][] = $product_variation;
	 
				endif;
			endforeach;
	 
			// Unset merge key. Don't need it anymore
			unset($product_data[$k]['_product_id']);
		endforeach;
	 
		return $product_data;
	}
	function status_message( $message ) {
		echo $message . "\r\n";
	}
    function getMatchingCategory($ocCateogry,$wpCategory,$oc2Wpcategories){
    	// right now do nothing later I will write details.
    	return 0;
    }	
    static public function slugify($text)
	{
	  // replace non letter or digits by -
	  $text = preg_replace('~[^\pL\d]+~u', '-', $text);

	  // transliterate
	  $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

	  // remove unwanted characters
	  $text = preg_replace('~[^-\w]+~', '', $text);

	  // trim
	  $text = trim($text, '-');

	  // remove duplicate -
	  $text = preg_replace('~-+~', '-', $text);

	  // lowercase
	  $text = strtolower($text);

	  if (empty($text)) {
	    return 'n-a';
	  }

	  return $text;
	}

	function getCategories(){
    	$obj = json_decode(file_get_contents('http://www.elektroniksigaraevi.us/kargo/generate_json_catergories.php'));		
    	return $obj;
    } 
    function getProdcucts(){
    	$products = json_decode(file_get_contents('http://www.elektroniksigaraevi.us/kargo/generate_json_products.php'));
    	return $products;
    }

    public function getTags(){
    	$obj = json_decode(file_get_contents('http://www.elektroniksigaraevi.us/kargo/generate_tags.php'));		
    	return $obj;

    }

	function postTags($woo){
		$tagIds = [];		
		$tags = $this->getTags();
	    foreach ($tags as $tag) {
	    	$currentTag = [
	    		'name' => $tag,
	    		'slug' => $this->slugify($tag)
	    	];
	    	$addedTag = $woo->post('products/tags', $currentTag);
	    	$tagIds[$tag] = $addedTag['id'];
	    }
	    return $tagIds;	
	}

	function postCategories($woo){
		$oc2Wpcategories = [];
	    $categories = $this->getCategories();
	    foreach ($categories as $cat) {
	    	//echo $cat->parent."<br>";
	    	if ($cat->parent <> '0' && $cat->id > $cat->parent)
	    		$cat->parent = $oc2Wpcategories[$cat->parent];// to be changed
	    	else 
	    		$cat->parent = 0;

	    	if ($cat->image <>'') {
	    		$cat->image = "http://www.elektroniksigaraevi.us/image/".$cat->image;
	    		# code...
	    	}
	    	$category = [
	    		'name'         => $cat->name,
	    		'slug'         => $cat->slug,
	    		'parent'       => $cat->parent,
	    		'description'  => $cat->description,
	    		'image'        => ['src'=> $cat->image ]
	    	];
	    	$added_element = $woo->post('products/categories', $category);
	    	//var_dump($added_element);
	    	//$element = json_decode($added_element);
	    	//var_dump($added_element);
	    	$oc2Wpcategories[$cat->id] = $added_element['id'];

	    	# code...
	    }
	    return $oc2Wpcategories;
	}

	public function dashboard(){
		$woo = new Client('http://elektroniksigarasi.org','ck_50cdc4f4e1f7fd654a478ebd2a2e7ca63c407eae',
    		'cs_399fe3ab9d66def517bf744c942fec2edf6b4a88',
	    [
	        'wp_api' => true,
	        'version' => 'wc/v2'
	    ]);

	    /// we added all the tags no needed to rerun it, we will need to do it once to match tags with added products
	    
    	$oc2Wpcategories = $this->postCategories($woo);
    	//$indexTags = $this->postTags($woo);

	    $products = $this->getProdcucts();
	    foreach ($products as $product) {
	    	// we have to do tags matching and category matching, 
	    	// tags are also an array.
	    	$wpCategory = [];
	    	$i = 0;
	    	//$ocTags = explode(',', $product->tags);
	    	$wpTags = [];
	    	/*foreach ($product->tags as $ocTag) {
	    		$ocTag= strtolower(trim($ocTag));
	    		$ocTag = $indexTags[$ocTag];
	    		$wpTags[$i] = $ocTag;
	    		$i++;
	    	}*/
	    	$i = 0;
	    	foreach ((array) $product->categories as $cat) {
	    		$wpCategory[$i] = ['id' => $oc2Wpcategories[$cat]];
	    		$i++;
	    	}
	    	//var_dump($wpCategory);
	    	//var_dump($wpTags);
	    	if ($product->image <>'') {
	    		//var_dump($product->image);
	    		//$product->image = "http://www.elektroniksigaraevi.us/image/".$product->image;
	    		$product = [
	    			'name'          => $product->name,
	    			'slug'          => $product->slug,
	    			'type'          => 'simple',
	    			'description'   => $product->description,
	    			'sku'           => $product->sku,
	    			'regular_price' => $product->regular_price,
					'sale_price'    => $product->sale_price,
					'tax_status'	=> 'none',
					'stock_quantity'=> $product->stock_quantity,
					'tags'          => 0,
					'images'        => $product->image,
					'categories'    => $wpCategory
 	    		];
 	    		//var_dump($product);
 	    		$woo->post('products',$product);
	    		# code...
	    	}	    	
	    	# code...
	    }
	    


	    /*$data = [
	    'name' => 'Clothing',
	    'image' => [
	        'src' => 'http://demo.woothemes.com/woocommerce/wp-content/uploads/sites/56/2013/06/T_2_front.jpg'
		    ]
		];

		print_r($woo->post('products/categories', $data));*/

	    //var_dump($categories);
    	// generate all categories with their parents,
    	// get data from opencart database, 
		//print_r($woocommerce->get('products/745'));
		//print_r($woocommerce->post('products', $data));
    	
		/*try {
		 
			$json = $this->parse_json( 'products.json' );
			//dd($json);
		 
			// Import Attributes
			foreach ( $this->get_attributes_from_json( $json ) as $product_attribute_name => $product_attribute ) :
		 
				$attribute_data = array(
				    'name' => $product_attribute_name,
				    'slug' => 'pa_' . strtolower( $product_attribute_name ),
				    'type' => 'select',
				    'order_by' => 'menu_order',
				    'has_archives' => true
				);
		 		//dd($attribute_data);
				$wc_attribute = $woo->post( 'products/attributes', $attribute_data );
		 
				if ( $wc_attribute ) :
					$this->status_message( 'Attribute added. ID: '. $wc_attribute['id'] );
		 
					// store attribute ID so that we can use it later for creating products and variations
					$added_attributes[$product_attribute_name]['id'] = $wc_attribute['id'];
					
					// Import: Attribute terms
					foreach ( $product_attribute['terms'] as $term ) :
		 
						$attribute_term_data = array(
							'name' => $term
						);
		 
						$wc_attribute_term = $woo->post( 'products/attributes/'. $wc_attribute['id'] .'/terms', $attribute_term_data );
		 
						if ( $wc_attribute_term ) :
							$this->status_message( 'Attribute term added. ID: '. $wc_attribute['id'] );
		 
							// store attribute terms so that we can use it later for creating products
							$added_attributes[$product_attribute_name]['terms'][] = $term;
						endif;	
						
					endforeach;
		 
				endif;		
		 
			endforeach;
		 
		 
			$data = $this->get_products_and_variations_from_json( $json, $added_attributes );
		 
			// Merge products and product variations so that we can loop through products, then its variations
			$product_data = $this->merge_products_and_variations( $data['products'], $data['product_variations'] );

			dd($product_data);
		 
			// Import: Products
			foreach ( $product_data as $k => $product ) :
		 
				if ( isset( $product['variations'] ) ) :
					$_product_variations = $product['variations']; // temporary store variations array
		 
					// Unset and make the $product data correct for importing the product.
					unset($product['variations']);
				endif;		
		 
					$wc_product = $woo->post( 'products', $product );
		 
					if ( $wc_product ) :
						$this->status_message( 'Product added. ID: '. $wc_product['id'] );
					endif;

				if ( isset( $_product_variations ) ) :
					// Import: Product variations
		 
					// Loop through our temporary stored product variations array and add them
					foreach ( $_product_variations as $variation ) :
						$wc_variation = $woo->post( 'products/'. $wc_product['id'] .'/variations', $variation );
		 
						if ( $wc_variation ) :
							$this->status_message( 'Product variation added. ID: '. $wc_variation['id'] . ' for product ID: ' . $wc_product['id'] );
						endif;	
					endforeach;	
		 
					// Don't need it anymore
					unset($_product_variations);
				endif;
		 
			endforeach;
			
		 
			} catch ( HttpClientException $e ) {
			    echo $e->getMessage(); // Error message
			}*/

	}
}
