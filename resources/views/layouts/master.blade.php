<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ShanKomeeProvider | Dashboard</title>

    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">

    <link rel="stylesheet" href="{{ asset('plugins/fontawesome-free/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/icheck-bootstrap/icheck-bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/jqvmap/jqvmap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/adminlte.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/overlayScrollbars/css/OverlayScrollbars.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/daterangepicker/daterangepicker.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/summernote/summernote-bs4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/sweetalert2-theme-bootstrap-4/bootstrap-4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/toastr/toastr.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('/plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />

    <!-- DataTables CSS -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.css">
    <link rel="stylesheet" type="text/css"
        href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.dataTables.min.css">

    {{-- @vite(['resources/js/app.js']) --}}


    <style>
        .dropdown-menu {
            z-index: 1050 !important;
        }
    </style>

    @yield('style')


</head>

<body class="hold-transition sidebar-mini layout-fixed">
    <div class="wrapper">



        <!-- Navbar -->
        <nav class="main-header navbar navbar-expand navbar-white navbar-light sticky-top">
            <!-- Left navbar links -->
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i
                            class="fas fa-bars"></i></a>
                </li>
                <li class="nav-item d-none d-sm-inline-block">
                    <a href="{{ route('home') }}" class="nav-link">Home</a>
                </li>
            </ul>



            <!-- Right navbar links -->
            <ul class="navbar-nav ml-auto">

                <!--begin::Messages Dropdown Menu-->
                
                <!--end::Messages Dropdown Menu-->
                <li class="nav-item">
                    <a class="nav-link"
                        href="{{ route('admin.changePassword', \Illuminate\Support\Facades\Auth::id()) }}">
                        {{ auth()->user()->name }}
                        @if (auth()->user()->referral_code)
                            | {{ auth()->user()->referral_code }}
                        @endif
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" data-toggle="dropdown" href="#">
                        | Balance: {{ number_format(auth()->user()->wallet->balanceFloat, 2) }}
                    </a>
                </li>

                <li class="nav-item dropdown">
                    <a class="nav-link" href="#"
                        onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                        <i class="fas fa-sign-out-alt"></i>
                    </a>

                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                        @csrf
                    </form>

                </li>

            </ul>
        </nav>
        <!-- /.navbar -->

        <!-- Main Sidebar Container -->
        <aside class="main-sidebar sidebar-dark-primary elevation-4">
            <!-- Brand Logo -->
             <a href="{{ route('home') }}" class="brand-link">
            <img src="{{ asset('img/city_slot_logo.jpg') }}" alt="AdminLTE Logo"
                class="brand-image img-circle elevation-3" style="opacity: .8">
            <span class="brand-text font-weight-light">ShanKomeeProvider</span>
            </a> 
            <!-- Brand Logo -->

            <!-- <a href="{{ route('home') }}" class="brand-link">
                <img src="{{ $adminLogo }}" alt="Admin Logo" class="brand-image img-circle elevation-3"
                    style="opacity: .8">
                {{-- <span class="brand-text font-weight-light">GoldenJack</span> --}}
                <span class="brand-text font-weight-light">{{ $siteName }}</span>
            </a> -->


            <!-- Sidebar -->
            <div class="sidebar">
                <nav class="mt-2">
                    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu"
                        data-accordion="false">
                        <li class="nav-item menu-open">
                            <a href="{{ route('home') }}"
                                class="nav-link {{ Route::current()->getName() == 'home' ? 'active' : '' }}">
                                <i class="nav-icon fas fa-tachometer-alt"></i>
                                <p>
                                    Dashboard
                                </p>
                            </a>
                        </li>

                       
                       
                    
                        @can('agent_index')
                            <li class="nav-item">
                                <a href="{{ route('admin.agent.index') }}"
                                    class="nav-link {{ Route::current()->getName() == 'admin.agent.index' ? 'active' : '' }}">
                                    <i class="fas fa-users"></i>
                                    <p>
                                        Agent List
                                    </p>
                                </a>
                            </li>
                        @endcan
                        @can('transfer_log')
                            <li class="nav-item">
                                <a href="{{ route('admin.player.index') }}"
                                    class="nav-link {{ Route::current()->getName() == 'admin.player.index' ? 'active' : '' }}">
                                    <i class="far fa-user"></i>
                                    <p>
                                        Player List
                                    </p>
                                </a>
                            </li>
                        @endcan
                        @can('contact')
                            <!-- <li class="nav-item">
                                <a href="{{ route('admin.contact.index') }}"
                                    class="nav-link {{ Route::current()->getName() == 'admin.contact.index' ? 'active' : '' }}">
                                    <i class="fas fa-address-book"></i>
                                    <p>
                                        Contact
                                    </p>
                                </a>
                            </li> -->
                        @endcan
                        @can('bank')
                            <!-- <li class="nav-item">
                                <a href="{{ route('admin.bank.index') }}"
                                    class="nav-link {{ Route::current()->getName() == 'admin.bank.index' ? 'active' : '' }}">
                                    <i class="fas fa-university"></i>
                                    <p>
                                        Bank
                                    </p>
                                </a>
                            </li> -->
                        @endcan
                        @if(Auth::user()->hasPermission('process_withdraw'))
                            <!-- <li class="nav-item">
                                <a href="{{ route('admin.agent.withdraw') }}"
                                    class="nav-link {{ Route::current()->getName() == 'admin.agent.withdraw' ? 'active' : '' }}">
                                    <i class="fas fa-comment-dollar"></i>
                                    <p>
                                        WithDraw Request
                                    </p>
                                </a>
                            </li> -->
                        @endif
                        @if(Auth::user()->hasPermission('process_deposit') || Auth::user()->hasPermission('view_deposit_requests'))
                            <!-- <li class="nav-item">
                                <a href="{{ route('admin.agent.deposit') }}"
                                    class="nav-link {{ Route::current()->getName() == 'admin.agent.deposit' ? 'active' : '' }}">
                                    <i class="fab fa-dochub"></i>
                                    <p>
                                        Deposit Request
                                    </p>
                                </a>
                            </li> -->
                        @endif
                        @can('transfer_log')
                        <li class="nav-item">
                            <a href="{{ route('admin.transfer-logs.index') }}"
                                class="nav-link {{ Route::current()->getName() == 'admin.transfer-logs.index' ? 'active' : '' }}">
                                <i class="fas fa-exchange-alt"></i>
                                <p>
                                    Transaction Log
                                </p>
                            </a>
                        </li>
                        @endcan
                       
                        @can('agent_access')
                            <!-- <li class="nav-item">
                                <a href="{{ route('admin.subacc.index') }}"
                                    class="nav-link {{ Route::current()->getName() == 'admin.subacc.index' ? 'active' : '' }}">
                                    <i class="fas fa-user-plus"></i>
                                    <p>
                                        Sub Agent Account
                                    </p>
                                </a>
                            </li> -->
                            <!-- <li class="nav-item">
                                <a href="{{ route('admin.agent.profile', auth()->user()->id) }}"
                                    class="nav-link {{ Route::current()->getName() == 'admin.agent.profile' ? 'active' : '' }}">
                                    <i class="fas fa-user-plus"></i>
                                    <p>
                                        Agent Profile
                                    </p>
                                </a>
                            </li> -->
                            <!-- <li class="nav-item">
                            <a href="{{ route('admin.reports.daily_win_loss') }}"
                                class="nav-link {{ Route::currentRouteName() == 'admin.reports.daily_win_loss' ? 'active' : '' }}">
                                <i class="nav-icon fas fa-chart-line"></i>
                                <p>
                                    Daily Win/Loss
                                </p>
                            </a>
                        </li> -->

                       
                        @endcan
                        @can('player_view')
                            <!-- <li class="nav-item">
                                <a href="{{ route('admin.subacc.profile', auth()->user()->id) }}"
                                    class="nav-link {{ Route::current()->getName() == 'admin.subacc.profile' ? 'active' : '' }}">
                                    <i class="fas fa-user-plus"></i>
                                    <p>
                                        Sub Agent Profile
                                    </p>
                                </a>
                            </li>

                            <li class="nav-item">
                                <a href="{{ route('admin.subacc.agent_players', auth()->user()->id) }}"
                                    class="nav-link {{ Route::current()->getName() == 'admin.subacc.agent_players' ? 'active' : '' }}">
                                    <i class="fas fa-user-plus"></i>
                                    <p>
                                        PlayerList
                                    </p>
                                </a>
                            </li> -->
                            <!-- <li class="nav-item">
                                <a href="{{ route('admin.subacc.tran.logs') }}"
                                    class="nav-link {{ Route::current()->getName() == 'admin.subacc.tran.logs' ? 'active' : '' }}">
                                    <i class="fas fa-exchange-alt"></i>
                                    <p>
                                        Transfer Log
                                    </p>
                                </a>
                            </li> -->
                        
                        @endcan

                        @can('player_view')

                        <!-- <li class="nav-item">
                                <a href="{{ route('admin.subacc.tran.logs') }}"
                                    class="nav-link {{ Route::current()->getName() == 'admin.subacc.tran.logs' ? 'active' : '' }}">
                                    <i class="fas fa-exchange-alt"></i>
                                    <p>
                                    Transaction Log
                                    </p>
                                </a>
                            </li> -->
                           
                        @endcan
                        @can('owner_access')
                            <li
                                class="nav-item">
                                <a href="#" class="nav-link">
                                    <i class="fas fa-tools"></i>
                                    <p>
                                        GSCPLUS Settings
                                        <i class="fas fa-angle-left right"></i>
                                    </p>
                                </a>
                                <ul class="nav nav-treeview">
                                    <li class="nav-item">
                                        <a href="{{ route('admin.gameLists.index') }}"
                                            class="nav-link {{ Route::current()->getName() == 'admin.gameLists.index' ? 'active' : '' }}">
                                            <i class="far fa-circle nav-icon"></i>
                                            <p>GSCPLUS GameList</p>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ route('admin.gametypes.index') }}"
                                            class="nav-link {{ Route::current()->getName() == 'admin.gametypes.index' ? 'active' : '' }}">
                                            <i class="far fa-circle nav-icon"></i>
                                            <p>GSCPLUS Provider</p>
                                        </a>
                                    </li>

                                    <!-- <li class="nav-item">
                                        <a href=""
                                            class="nav-link">
                                            <i class="far fa-circle nav-icon"></i>
                                            <p>GSC GameType</p>
                                        </a>
                                    </li> -->
                                </ul>
                            </li>
                        @endcan

                        <!-- 2D -->

                        @can('owner_access')
                            <!-- <li
                                class="nav-item">
                                <a href="#" class="nav-link">
                                    <i class="fas fa-tools"></i>
                                    <p>
                                        2D
                                        <i class="fas fa-angle-left right"></i>
                                    </p>
                                </a>
                                <ul class="nav nav-treeview">
                                    <li class="nav-item">
                                        <a href="{{ route('admin.twod.settings') }}"
                                            class="nav-link {{ Route::current()->getName() == 'admin.twod.settings' ? 'active' : '' }}">
                                            <i class="far fa-circle nav-icon"></i>
                                            <p>2D Settings</p>
                                        </a>
                                    </li>

                                        <li class="nav-item">
                                            <a href="{{ route('admin.twod.bet-slip-list') }}"
                                                class="nav-link {{ Route::current()->getName() == 'admin.twod.bet-slip-list' ? 'active' : '' }}">
                                                <i class="fas fa-list-alt nav-icon"></i>
                                                <p>2D Bet Slip List</p>
                                            </a>
                                        </li>

                                        <li class="nav-item">
                                            <a href="{{ route('admin.twod.daily-ledger') }}"
                                                class="nav-link {{ Route::current()->getName() == 'admin.twod.daily-ledger' ? 'active' : '' }}">
                                                <i class="fas fa-book nav-icon"></i>
                                                <p>2D Daily Ledger</p>
                                            </a>
                                        </li>

                                        <li class="nav-item">
                                            <a href="{{ route('admin.twod.daily-winners') }}"
                                                class="nav-link {{ Route::current()->getName() == 'admin.twod.daily-winners' ? 'active' : '' }}">
                                                <i class="fas fa-trophy nav-icon"></i>
                                                <p>2D Daily Winners</p>
                                            </a>
                                        </li>

                                    
                                </ul>
                            </li> -->
                        @endcan


                        <!-- agent 2d -->
                        @can('agent_access')
                            <!-- <li
                                class="nav-item">
                                <a href="#" class="nav-link">
                                    <i class="fas fa-tools"></i>
                                    <p>
                                        2D
                                        <i class="fas fa-angle-left right"></i>
                                    </p>
                                </a>
                                <ul class="nav nav-treeview">
                                    

                                <li class="nav-item">
                                            <a href="{{ route('admin.twod.bet-slip-list') }}"
                                                class="nav-link {{ Route::current()->getName() == 'admin.twod.bet-slip-list' ? 'active' : '' }}">
                                                <i class="fas fa-list-alt nav-icon"></i>
                                                <p>2D Bet Slip List</p>
                                            </a>
                                        </li>

                                        <li class="nav-item">
                                            <a href="{{ route('admin.twod.daily-ledger') }}"
                                                class="nav-link {{ Route::current()->getName() == 'admin.twod.daily-ledger' ? 'active' : '' }}">
                                                <i class="fas fa-book nav-icon"></i>
                                                <p>2D Daily Ledger</p>
                                            </a>
                                        </li>

                                        <li class="nav-item">
                                            <a href="{{ route('admin.twod.daily-winners') }}"
                                                class="nav-link {{ Route::current()->getName() == 'admin.twod.daily-winners' ? 'active' : '' }}">
                                                <i class="fas fa-trophy nav-icon"></i>
                                                <p>2D Daily Winners</p>
                                            </a>
                                        </li>
                                </ul>
                            </li> -->
                        @endcan

                        <!-- agent 2d -->
                        
                        <li
                            class="nav-item">
                            <a href="#" class="nav-link">
                                <i class="fas fa-file-invoice"></i>
                                <p>
                                    Reports
                                    <i class="fas fa-angle-left right"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                            <!-- <li class="nav-item menu-open">
                                    <a href="{{ route('admin.wager-list') }}"
                                        class="nav-link {{ Route::current()->getName() == 'admin.wager-list' ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>
                                            Wager List
                                        </p>
                                    </a>
                                </li> -->
                                @can('agent_access')
                                <!-- <li class="nav-item">
                            <a href="{{ route('admin.player_report.summary') }}"
                                class="nav-link {{ Route::currentRouteName() == 'admin.player_report.summary' ? 'active' : '' }}">
                                <i class="nav-icon fas fa-chart-line"></i>
                                <p>
                                    Win/Lose Report
                                </p>
                            </a>
                        </li> -->
                        @endcan
                                @can('owner_access')
                                <!-- <li class="nav-item menu-open">
                                    <a href="{{ route('admin.report.index') }}"
                                        class="nav-link {{ Route::current()->getName() == 'admin.report.index' ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>
                                            Win/Lose Report
                                        </p>
                                    </a>
                                </li> -->
                                @endcan

                                @can('player_view')
                                <!-- <li class="nav-item">
                            <a href="{{ route('admin.player_report.summary') }}"
                                class="nav-link {{ Route::currentRouteName() == 'admin.player_report.summary' ? 'active' : '' }}">
                                <i class="nav-icon fas fa-chart-line"></i>
                                <p>
                                    Win/Lose Report
                                </p>
                            </a>
                        </li> -->
                        @endcan
                                
                                
                                <li
                                class="nav-item">
                                <a href="#" class="nav-link">
                                    <i class="fas fa-tools"></i>
                                    <p>
                                        Shan Player Report
                                        <i class="fas fa-angle-left right"></i>
                                    </p>
                                </a>
                                <ul class="nav nav-treeview">
                                <!-- <li class="nav-item">
                                        <a href="{{ route('admin.shan.player.report') }}"
                                        class="nav-link {{ Route::current()->getName() == 'admin.shan.player.report' ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Shan Player Report</p>
                                    </a>
                                </li> -->
                                <li class="nav-item">
                                        <a href="{{ route('admin.shan.player.report.grouped') }}"
                                        class="nav-link {{ Route::current()->getName() == 'admin.shan.player.report.grouped' ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>ShanReport</p>
                                    </a>
                                </li>
                                </ul>
                            </li>
                                
                            </ul>
                        </li>
                        @can('owner_access')
                        <li class="nav-item {{ in_array(Route::currentRouteName(), ['admin.text.index', 'admin.banners.index', 'admin.adsbanners.index', 'admin.promotions.index']) ? 'menu-open' : '' }}">
    <a href="#" class="nav-link">
        <i class="fas fa-tools"></i>
        <p>
            General Settings
            <i class="fas fa-angle-left right"></i>
        </p>
    </a>
    <ul class="nav nav-treeview">
        <li class="nav-item">
            <a href="{{ route('admin.logs.index') }}"
               class="nav-link {{ Route::current()->getName() == 'admin.logs.index' ? 'active' : '' }}">
                <i class="fas fa-video nav-icon"></i>
                <p>Laravel Log</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('admin.video-upload.index') }}"
               class="nav-link {{ Route::current()->getName() == 'admin.video-upload.index' ? 'active' : '' }}">
                <i class="fas fa-video nav-icon"></i>
                <p>AdsVideo</p>
            </a>
        </li>

       

        <li class="nav-item">
            <a href="{{ route('admin.text.index') }}"
               class="nav-link {{ Route::current()->getName() == 'admin.text.index' ? 'active' : '' }}">
                <i class="fas fa-font nav-icon"></i>
                <p>BannerText</p>
            </a>
        </li>

        <li class="nav-item">
            <a href="{{ route('admin.banners.index') }}"
               class="nav-link {{ Route::current()->getName() == 'admin.banners.index' ? 'active' : '' }}">
                <i class="fas fa-image nav-icon"></i>
                <p>Banner</p>
            </a>
        </li>

        <li class="nav-item">
            <a href="{{ route('admin.adsbanners.index') }}"
               class="nav-link {{ Route::current()->getName() == 'admin.adsbanners.index' ? 'active' : '' }}">
                <i class="fas fa-ad nav-icon"></i>
                <p>Banner Ads</p>
            </a>
        </li>

        <li class="nav-item">
            <a href="{{ route('admin.promotions.index') }}"
               class="nav-link {{ Route::current()->getName() == 'admin.promotions.index' ? 'active' : '' }}">
                <i class="fas fa-bullhorn nav-icon"></i>
                <p>Promotions</p>
            </a>
        </li>
    </ul>
</li>
@endcan
    </nav>
                <!-- /.sidebar-menu -->
            </div>
            <!-- /.sidebar -->
        </aside>

        <div class="content-wrapper">

            @yield('content')
        </div>
        <footer class="main-footer">
            <strong>Copyright &copy; 2025 <a href="">ShanKomeeProvider</a>.</strong>
            All rights reserved.
            <div class="float-right d-none d-sm-inline-block">
                <b>Version</b> 3.2.2
            </div>
        </footer>

        <aside class="control-sidebar control-sidebar-dark">
        </aside>
    </div>
    
    <script src="{{ asset('plugins/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('plugins/jquery-ui/jquery-ui.min.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    <script>
        // $.widget.bridge('uibutton', $.ui.button)
    </script>
    <script src="{{ asset('plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('plugins/bootstrap/js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('plugins/moment/moment.min.js') }}"></script>
    <script src="{{ asset('plugins/daterangepicker/daterangepicker.js') }}"></script>
    <script src="{{ asset('plugins/summernote/summernote-bs4.min.js') }}"></script>
    <script src="{{ asset('plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js') }}"></script>
    <script src="{{ asset('js/adminlte.js') }}"></script>
    <script src="{{ asset('js/dashboard.js') }}"></script>
    <script src="{{ asset('plugins/sweetalert2/sweetalert2.min.js') }}"></script>
    <script src="{{ asset('plugins/toastr/toastr.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>

    <!-- DataTables JS -->
    <script type="text/javascript" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.html5.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.print.min.js"></script>

    @yield('script')
    <script>
        var errorMessage = @json(session('error'));
        var successMessage = @json(session('success'));

        @if (session()->has('success'))
            toastr.success(successMessage)
        @elseif (session()->has('error'))
            toastr.error(errorMessage)
        @endif
    </script>
    <script>
        $(function() {
            $('.select2bs4').select2({
                theme: 'bootstrap4'
            });
            $('#ponewineTable').DataTable();
            $('#slotTable').DataTable();

            $("#mytable").DataTable({
                "responsive": true,
                "lengthChange": false,
                "autoWidth": false,
                "order": true,
                "pageLength": 10,
            }).buttons().container().appendTo('#example1_wrapper .col-md-6:eq(0)');
        });
    </script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var dropdownElementList = [].slice.call(document.querySelectorAll('.dropdown-toggle'))
            var dropdownList = dropdownElementList.map(function(dropdownToggleEl) {
                return new bootstrap.Dropdown(dropdownToggleEl)
            })
        });
    </script>
    
    

</body>

</html>
