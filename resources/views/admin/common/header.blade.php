<button class="navbar-toggler mobile-sidebar-toggler d-lg-none" type="button">
    <span class="navbar-toggler-icon"></span>
</button>
<a class="navbar-brand" href="/"></a>
<button class="navbar-toggler sidebar-toggler d-md-down-none" type="button">
    <span class="navbar-toggler-icon"></span>
</button>
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
            <a class="dropdown-item" href="/admin/users"><i class="icon-user"></i> User Management</a>
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
