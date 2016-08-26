@extends('app/master')

@section('body')

	@if(Session::has('message'))
	    <div class="alert alert-success">
	        <p>{{ Session::get('message') }}</p>
	    </div>
	@endif
	
	<table class="table">
			<tr>
				<th><center><a href="{{url('data/scrapedDataDownload?filefor=email')}}"><abbr title="Download Scraped Email Data">Email</abbr></a></center></th>
				<th><center><a href="{{url('data/scrapedDataDownload?filefor=phone')}}"><abbr title="Download Scraped Phone Data">Phone</abbr></a></center></th>
				<th><center><a href="{{url('data/scrapedDataDownload?filefor=name')}}"><abbr title="Download Scraped Name Data">Name</abbr></a></center></th>
				<th><center><a href="{{url('data/scrapedDataDownload?filefor=title')}}"><abbr title="Download Scraped Title Data">Title</abbr></a></center></th>
			</tr>
		@forelse( $leads as $lead )
			<tr>
				<td>{{ $lead->email }}</td>
				<td>{{ $lead->phone }}</td>
				<td>{{ $lead->name }}</td>
				<td>{{ $lead->title }}</td>
			</tr>
		@empty
			<tr>
				<td colspan="4"><h2>No Data Found</h2></td>
			</tr>
		@endforelse
	</table>

	<div class="well">
        <a class="btn btn-default btn-lg btn-block" href="{{url('data/scrapedDataDownload?filefor=all')}}">Download Scraped Data</a>
    </div>

@stop