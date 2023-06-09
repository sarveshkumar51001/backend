<nav class="sidebar-nav open">
    <ul class="nav">
        <li class="nav-item">

            <a class="nav-link" href="{{ URL::to('/') }}"><i class="icon-speedometer"></i> Dashboard </a>
        </li>
{{--        <li class="nav-title">Students</li>--}}
{{--        <li class="nav-item nav-dropdown">--}}
{{--        <a class="nav-link nav-dropdown-toggle" href="#"><i class="fa fa-university"></i> Students </a>--}}
{{--            <ul class="nav-dropdown-items">--}}
{{--                <li class="nav-item">--}}
{{--                    <a class="nav-link" href="{{ URL::to('/students/search') }}"><i class="fa fa-search"></i>Student Search</a>--}}
{{--                </li>--}}
{{--            </ul>--}}
{{--        </li>--}}
        <li class="nav-item nav-dropdown">
            <a class="nav-link nav-dropdown-toggle" href="#"><i class="fa fa-shopping-cart"></i> Shopify</a>
            <ul class="nav-dropdown-items">
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('bulkupload.previous_orders') }}"><i class="fa fa-upload"></i>Bulk Upload</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ URL::to('/students/search') }}"><i class="fa fa-user-circle"></i>Student Search</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('bulkupload.installments') }}"><i class="fa fa-rupee"></i>Upcoming Instalments</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ URL::to('/shopify/products') }}"><i class="fa fa-shopping-cart"></i>Products</a>
                </li>
{{--                <li class="nav-item">--}}
{{--                    <a class="nav-link" href="{{ URL::to('/shopify/customers') }}"><i class="icon-user"></i>Customers</a>--}}
{{--                </li>--}}
{{--                <li class="nav-item">--}}
{{--                    <a class="nav-link" href="{{ URL::to('/shopify/orders') }}"><i class="fa fa-sitemap"></i>Orders</a>--}}
{{--                </li>--}}
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('bulkupload.search') }}"><i class="fa fa-search"></i>Order Search</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('revenue.reports') }}"><i class="fa fa-dashboard"></i> Reports</a>
                </li>
                @if(has_permission(\App\Library\Permission::PERMISSION_RECONCILE))
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('bulkupload.reconcile.index') }}"><i class="fa fa-money"></i>Reconcile</a>
                    </li>
                @endif
            </ul>
        </li>
{{--        <li class="nav-title">Customer profiler</li>--}}
{{--        <li class="nav-item nav-dropdown">--}}
{{--            <a class="nav-link nav-dropdown-toggle" href="#"><i class="icon-user"></i> Customer profiler</a>--}}
{{--            <ul class="nav-dropdown-items">--}}
{{--                <li class="nav-item">--}}
{{--                    <a class="nav-link" href="{{ URL::to('/customers/profiler') }}"><i class="icon-user"></i>Profiler result</a>--}}
{{--                </li>--}}
{{--            </ul>--}}
{{--        </li>--}}
{{--        <li class="nav-title">Image Recognitions</li>--}}
{{--        <li class="nav-item nav-dropdown">--}}
{{--            <a class="nav-link nav-dropdown-toggle" href="#"><i class="fa fa-search"></i> Search</a>--}}
{{--            <ul class="nav-dropdown-items">--}}
{{--                <li class="nav-item">--}}
{{--                    <a class="nav-link" href="{{ route('imagereco.list-all-people') }}"><i class="fa fa-search"></i>List People</a>--}}
{{--                    <a class="nav-link" href="{{ route('imagereco.search-by-image') }}"><i class="fa fa-search"></i>By image</a>--}}
{{--                    <a class="nav-link" href="{{ route('imagereco.search-by-name') }}"><i class="fa fa-search"></i>By Name</a>--}}
{{--                </li>--}}
{{--            </ul>--}}
{{--        </li>--}}

        <li class="nav-item nav-dropdown">
            <a class="nav-link nav-dropdown-toggle" href="#"><i class="fa fa-file"></i> Instapage</a>
            <ul class="nav-dropdown-items">
                <li class="nav-item">
                    <a class="nav-link" href="{{ URL::to('/pages/leads') }}"><i class="fa fa-sitemap"></i> Leads </a>
                </li>
                @if(is_admin())
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('notifications.index') }}"><i class="fa fa-send"></i>Notifications</a>
                    </li>
                @endif
            </ul>
        </li>
        @if(\Module::has('Online'))
            <li class="nav-item nav-dropdown">
                <a class="nav-link nav-dropdown-toggle" href="#"><i class="fa fa-globe"></i> Online Sessions</a>
                <ul class="nav-dropdown-items">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ URL::to('/online/products') }}"><i class="fa fa-list"></i> Products </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="{{ URL::to('/online/orders') }}"><i class="fa fa-list"></i> Orders </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ URL::to('/online/sessions') }}"><i class="fa fa-clock-o"></i> Sessions </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ URL::to('/online/upload') }}"><i class="fa fa-upload"></i> Upload </a>
                    </li>
                </ul>
            </li>
        @endif
        @if(\Module::has('Payments'))
            <li class="nav-item nav-dropdown">
                <a class="nav-link nav-dropdown-toggle" href="#"><i class="fa fa-globe"></i> Payments</a>
                <ul class="nav-dropdown-items">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ URL::to('/payments/lists') }}"><i class="fa fa-list"></i> List </a>
                    </li>
                </ul>
            </li>
        @endif
    </ul>
    </nav>
<button class="sidebar-minimizer brand-minimizer" type="button"></button>
