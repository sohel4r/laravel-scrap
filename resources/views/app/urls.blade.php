@extends('app/master')

@section('body')

	@if (count($errors) > 0)
	    <div class="alert alert-danger">
	        <ul>
	            @foreach ($errors->all() as $error)
	                <li>{{ $error }}</li>
	            @endforeach
	        </ul>
	    </div>
	@endif

	@if(Session::has('message'))
	    <div class="alert alert-success">
	        <p>{{ Session::get('message') }}</p>
	    </div>
	@endif

	{!! Form::open(['url'=>'data/url', 'method'=>'POST']) !!}

		<div class="form-group">
			<label for="name">Url:</label>
			<input type="text" name="name" id="name" class="form-control" value="{{ old('name') }}">
		</div>
		<div class="checkbox">
		  	<label><input type="checkbox" name="chkoption[]" id="chkoption" value="emailChk" checked>Email</label>
		</div>
		<div class="checkbox">
		  	<label><input type="checkbox" name="chkoption[]" id="chkoption" value="nameChk">Name</label>
		</div>
		<div class="checkbox">
		  	<label><input type="checkbox" name="chkoption[]" id="chkoption" value="phoneChk">Phone No</label>
		</div>
		<div class="form-group">
			<button type="submit" class="btn btn-default">Scrap</button>
		</div>

	{!! Form::close() !!}
	
	@forelse($urls as $url)
		<li>{{ $url->name }} 
		<!-- <a href="{{ url('data/geturl').'?url='.$url->name }}">Scrap</a> -->
		 
		<!-- <a href="{{ url('data/links', $url->id) }}">View All Links</a> -->
		<a href="#" id="{{ $url->id }}" class="view-link">View Scrap Data</a>
		</li>
		<li>
			<div class="link-list{{ $url->id }}">
				
			</div>
		</li>
	@empty
		<h2>No Url Found</h2>
	@endforelse
@stop