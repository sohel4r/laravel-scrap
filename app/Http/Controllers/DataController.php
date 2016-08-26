<?php

namespace App\Http\Controllers;

use Excel;
use App\Url;
use Session;
use App\Lead;
use Goutte\Client;
use App\Http\Requests;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redirect;

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
		
		$requesturl = $request->input('name');
		$scrapedurl = Url::where('name', $requesturl)->firstOrFail();

		$this->urlId = $scrapedurl->id;

		$crawler = $this->helper_crawler($scrapedurl->name);

		$isBlock = $crawler->filter('p')->text();

		//dd($crawler->html());

		if(strpos($isBlock,'blocked') != false ) {
			echo "Your ip is blocked. Please try again later";
			die();

		} else {

				$data = $crawler->filterXpath("//div[@class='rows']");
				$data->filter('p > a')->each(function ($node){
					$scrapedurl = $node->attr('href');

					if( ! preg_match("/\/\/.+/", $scrapedurl)) {

						
						$this->getInfo($scrapedurl);

					}
				});	
		}
		$leads = Lead::all();
		Session::flash('leads', $leads);
		return redirect()->back()->with('message', "Link was scraped please view link");
	}

	/**
	 * @return get all assosiative link form this url and insert all url
	 * to database
	 */

	public function getGeturl(Request $request)
	{	
		// go up
	}

	public function scrapedDataDownload(Request $request)
	{	
		$downloadFor = $request->input('filefor');
		
		if ($downloadFor == "all")
		{
			$logs = Lead::select('email as Email_Address', 'phone as Phone_Number', 'name as Name', 'title as Title')
                            ->get();
		}
		elseif ($downloadFor == "email")
		{
			$logs = Lead::select('email as Email_Address')
                            ->get();
		}
		elseif ($downloadFor == "phone")
		{
			$logs = Lead::select('phone as Phone_Number')
                            ->get();
		}
		elseif ($downloadFor == "name")
		{
			$logs = Lead::select('name as Name')
                            ->get();
		}
		elseif ($downloadFor == "title")
		{
			$logs = Lead::select('title as Title')
                            ->get();
		}
		else
		{
			return redirect()->back()->with('message', "This download link is broken, please try again later");
		}

        Excel::create('ScrapedData', function($excel) use($logs,$downloadFor)
        {
            $excel->sheet('Sheet 1', function($sheet) use($logs,$downloadFor)
            {
                $sheet->fromArray($logs);

                $sheet->prependRow(1, array(
                    'Report For : '.date("Y-M-d")
                ));
                
                $sheet->mergeCells('A1:D1');
            });
        })->export('xls');
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