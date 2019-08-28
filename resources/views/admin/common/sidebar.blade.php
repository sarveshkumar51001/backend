<nav class="sidebar-nav">
    <ul class="nav">
        <li class="nav-item">
            <a class="nav-link" href="{{ URL::to('/') }}"><i class="icon-speedometer"></i> Dashboard </a>
        <li class="nav-title">Shopify</li>
        <li class="nav-item nav-dropdown">
            <a class="nav-link nav-dropdown-toggle" href="#"><i class="fa fa-database"></i> Shopify</a>
            <ul class="nav-dropdown-items">
                <li class="nav-item">
                    <a class="nav-link" href="{{ URL::to('/products') }}"><i class="fa fa-shopping-cart"></i>Products</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ URL::to('/customers') }}"><i class="icon-user"></i>Customers</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ URL::to('/orders') }}"><i class="fa fa-sitemap"></i>Orders</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('bulkupload.previous_orders') }}"><i class="fa fa-upload"></i>Bulk Upload</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('search.students') }}"><i class="fa fa-university"></i>Students</a>
                </li>
            </ul>
        </li>
        <li class="nav-title">Customer profiler</li>
        <li class="nav-item nav-dropdown">
            <a class="nav-link nav-dropdown-toggle" href="#"><i class="icon-user"></i> Customer profiler</a>
            <ul class="nav-dropdown-items">
                <li class="nav-item">
                    <a class="nav-link" href="{{ URL::to('/customers/profiler') }}"><i class="icon-user"></i>Profiler result</a>
                </li>
            </ul>
        </li>
            <li class="nav-title">Image Recognitions</li>
            <li class="nav-item nav-dropdown">
                <a class="nav-link nav-dropdown-toggle" href="#"><i class="fa fa-search"></i> Search</a>
                <ul class="nav-dropdown-items">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('imagereco.list-all-people') }}"><i class="fa fa-search"></i>List People</a>
                        <a class="nav-link" href="{{ route('imagereco.search-by-image') }}"><i class="fa fa-search"></i>By image</a>
                        <a class="nav-link" href="{{ route('imagereco.search-by-name') }}"><i class="fa fa-search"></i>By Name</a>
                    </li>
                </ul>
            </li>
        </li>
        </li>
    </ul>
</nav>
<button class="sidebar-minimizer brand-minimizer" type="button"></button>
