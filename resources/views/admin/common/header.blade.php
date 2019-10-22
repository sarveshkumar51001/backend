<button class="navbar-toggler mobile-sidebar-toggler d-lg-none" type="button">
    <span class="navbar-toggler-icon"></span>
</button>
<a class="navbar-brand" href="{{action('HomeController@index')}}"></a>
<button class="navbar-toggler sidebar-toggler d-md-down-none" type="button">
    <span class="navbar-toggler-icon"></span>
</button>
<ul class="nav navbar-nav d-md-down-none mr-auto">
    <li class="nav-item px-3 pull-left">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="input-group custom-search-form">
                        <form method="get" action="{{action('SearchController@index')}}"><input value="{{ $query ?? '' }}" name="q" id="search" type="text" placeholder="Search anything here ..."></form>
                    </div>
                </div>
            </div>
        </div>
        <style>
            .custom-search-form input {
                border: 1px solid #ccc;
                padding: 8px;
                -moz-border-radius:50px;
                -webkit-border-radius:50px;
                border-radius:50px;
                width:300px;
                transition: 0.5s;
            }
            .custom-search-form input:focus {
                width:500px;
                transition: 0.5s;
            }
        </style>
    </li>
</ul>
<ul class="nav navbar-nav d-md-down-none mr-auto">
    <li class="nav-item px-3 pull-left">
    </li>
</ul>


<ul class="nav navbar-nav mr-2">
    <li class="nav-item dropdown">
        <a class="nav-link nav-link" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">
            <span>Welcome, {{ \Auth::user()->name ?? '' }} <i class="fa fa-angle-down"></i></span>
        </a>
        <div class="dropdown-menu dropdown-menu-right">
            <div class="dropdown-header text-center">
                <strong>Account</strong>
            </div>
            <a class="dropdown-item" href="{{ route('logout') }}"
               onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                <i class="fa fa-lock"></i> Logout
            </a>
            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                {{ csrf_field() }}
            </form>
        </div>
    </li>
</ul>
