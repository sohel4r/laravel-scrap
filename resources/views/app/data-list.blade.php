@extends('app/master')

@section('body')

	@if(Session::has('message'))
	    <div class="alert alert-success">
	        <p>{{ Session::get('message') }}</p>
	    </div>
	@endif
	
	<table class="table">
			<!-- <tr>
				<th><center><a href="{{url('data/scrapedDataDownload?filefor=email')}}"><abbr title="Download Scraped Email Data">Email</abbr></a></center></th>
				<th><center><a href="{{url('data/scrapedDataDownload?filefor=phone')}}"><abbr title="Download Scraped Phone Data">Phone</abbr></a></center></th>
				<th><center><a href="{{url('data/scrapedDataDownload?filefor=name')}}"><abbr title="Download Scraped Name Data">Name</abbr></a></center></th>
				<th><center><a href="{{url('data/scrapedDataDownload?filefor=title')}}"><abbr title="Download Scraped Title Data">Title</abbr></a></center></th>
			</tr> -->
			{!! Form::open(array('url' => 'data/scrapedDataDownload', 'method' => 'post')) !!}
			<tr>
				<th><center><input type="checkbox" name="fileforp[]" value="email">	Email 	</center></th>
				<th><center><input type="checkbox" name="fileforp[]" value="phone">	phone 	</center></th>
				<th><center><input type="checkbox" name="fileforp[]" value="name">	Name 	</center></th>
				<th><center><input type="checkbox" name="fileforp[]" value="title">	Title 	</center></th>
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

			<tr>
				<td colspan="4">					
					<button type="submit" class="btn btn-default btn-lg btn-block">Download Scraped Data</button>
				</td>
			</tr>

			{!! Form::close() !!}
			
	</table>

@stop