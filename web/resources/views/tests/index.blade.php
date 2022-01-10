@include('tests.layouts.header-calendar')
<div class="container">
    {{--@include('tests.layouts.menu')--}}

    <h1>{{ $page_name }}</h1>

    <div id="calendar"></div>
</div>

@include('tests.layouts.footer-calendar')
