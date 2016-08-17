<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests;
use App\Lead;
use App\Url;
use Goutte\Client;
use Illuminate\Http\Request;

class DataController extends Controller
{
    public $urlId;
    public $title;
    public $mapLocation;
    public $body;

	public function getIndex()
	{
		return view("app/index");
	}


	/**
	 * @return view data and add url
	 */

	public function getUrls()
	{
		$urls = Url::all();

		return view("app/urls", compact('urls'));		
	}
	/**
	 * @return insert data to url table
	 */

	public function postUrl(Request $request){

	    $this->validate($request, [
	        'name' => 'required|unique:urls|max:255',
	    ]);		
		$url = Url::create($request->all());
		
		return redirect()->back()->with('message',"Link insert was successfull");
	}

	/**
	 * @return get all assosiative link form this url and insert all url
	 * to database
	 */

	public function getGeturl(Request $request){

		$requesturl = $request->get('url');
		$url = Url::where('name', $requesturl)->firstOrFail();

		$this->urlId = $url->id;

		$crawler = $this->helper_crawler($url->name);

		$isBlock = $crawler->filter('p')->text();

		//dd($crawler->html());

		if(strpos($isBlock,'blocked') != false ) {
			echo "Your ip is blocked. Please try again later";
			die();

		} else {

				$data = $crawler->filterXpath("//div[@class='rows']");
				$data->filter('p > a')->each(function ($node){
					$url = $node->attr('href');

					if( ! preg_match("/\/\/.+/", $url)) {

						
						$this->getInfo($url);

					}
				});	
		}

		return redirect()->back()->with('message', "Link was scraped please view link");

	}

	/**
	 * Showing all urls 
	 */

	public function getUrllist()
	{
		$urls = Url::all();

		return view('app.url-list', compact('urls'));

	}

	public function getLinks($url)
	{

		$url = Url::findOrfail($url);
		
		return view('app.links', compact('url'));
	}

	/**
	 * @return Get user data from craglist
	 */

	public function getInfo($link)
	{
		//Get the url name
		$url = Url::findOrfail($this->urlId);

		if($url) {
			$ul = parse_url($url->name);
			$links = 'http://'.$ul['host'].$link;
		}

		$crawler = $this->helper_crawler($links);


		$isBlock = $crawler->filter('p')->text();

		if(strpos($isBlock,'blocked') != false ) {
			//next process and change ip
			echo "Ip Address is blocked";
			die();

		} else {

			if($crawler->filter('title')->count()) {
				$this->title = $crawler->filter('title')->text();
			}

	    	if($crawler->filterXPath('//div[@class="mapAndAttrs"]')->count()) {
	    		$this->mapLocation = $crawler->filterXPath('//div[@class="mapAndAttrs"]')->html();
	    	}

	    	if($crawler->filterXPath('//section[@id="postingbody"]')->count()) {
	    		$this->body = $crawler->filterXPath('//section[@id="postingbody"]')->html();
	    	}

			$lnk = $crawler->selectLink('reply')->link();

			//Ading user-agent
			$agent= 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/48.0.2564.82 Safari/537.36';
			
			$client = new Client(['HTTP_USER_AGENT' => $agent]);

			$crawler = $client->click($lnk);

			if ($crawler->filterXpath("//div[@class='captcha']")->count()) {

				//Next process and change ip
				echo "Captcha given wait few hours";

			} else {


				$name = $email = $mobile =  "";
				
				if($crawler->filterXPath('//ul[not(@class)]/li[not(div)]')->count()){
					$name = $crawler->filterXPath('//ul[not(@class)]/li[not(div)]')->text();
				}
		    	
		    	if($crawler->filterXPath('//ul/li/a[@class="mailapp"]')->count()) {
		    		$email = $crawler->filterXPath('//ul/li/a[@class="mailapp"]')->text();
		    	}
		    	
		    	if($crawler->filterXPath('//a[@class="mobile-only replytellink"]')->count()){
		    		$mb = $crawler->filterXPath('//a[@class="mobile-only replytellink"]')->attr('href');
		    		$mobile = str_replace("tel:", '', $mb);
		    	}
		    	
				$url->leads()->create(['link'=> $link, 'title'=> $this->title,'email'=>$email, 'name'=> $name, 'phone' => $mobile, 'mapLocation' => $this->mapLocation, 'body' => $this->body]);

			}
			
		}

			return redirect()->back()->with('message', "Please check scrap data");
		

	}

	/**
	 * @return @data list
	 */

	public function getInfolist()
	{
		$leads = Lead::all();
		return view('app.data-list', compact('leads'));
	}


	/**
	 * @return all data of that url
	 */

	public function helper_crawler($url)
	{

		$agent= 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/48.0.2564.82 Safari/537.36';
		$Accept = 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8';
		
		$client = new Client(['HTTP_USER_AGENT' => $agent]);		
		return  $client->request('GET', $url );			
	}	

}