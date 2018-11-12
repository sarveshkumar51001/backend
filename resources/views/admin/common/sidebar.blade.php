<nav class="sidebar-nav">
    <ul class="nav">
        <li class="nav-item">
            <a class="nav-link" href="{{ URL::to('/') }}"><i class="icon-speedometer"></i> Dashboard </a>
            <li class="nav-title">Shopify</li>
            <li class="nav-item nav-dropdown">
                <a class="nav-link nav-dropdown-toggle" href="#"><i class="fa fa-database"></i> Shopify orders</a>
                <ul class="nav-dropdown-items">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ URL::to('/orders') }}"><i class="fa fa-sitemap"></i> List orders</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ URL::to('/orders/create') }}"><i class="fa fa-users"></i> Create orders</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ URL::to('/orders/create') }}"><i class="fa fa-users"></i> Payment reminders</a>
                    </li>
                </ul>
            </li>
        </li>
    </ul>
</nav>
<button class="sidebar-minimizer brand-minimizer" type="button"></button>
