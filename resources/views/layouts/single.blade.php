<!DOCTYPE html>
<html lang="en">
    @include('common.single-head')
    <body id="landing-layout">
		@include('common.single-header') 
		<div class="landing-content">
			@include('common.single-nav')
			@yield('content')
		</div>
		@include('common.single-footer')
	</body>
</html>