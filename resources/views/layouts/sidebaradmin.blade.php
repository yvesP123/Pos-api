@if(auth()->user()->level == 1)
<li class="treeview">
    <a href="#">
        <i class="fa fa-key"></i>
        <span>License Management</span>
        <span class="pull-right-container">
            <i class="fa fa-angle-left pull-right"></i>
        </span>
    </a>
    <ul class="treeview-menu">
        <li><a href="{{ route('license-management.index') }}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
        <li><a href="{{ route('license-management.create') }}"><i class="fa fa-plus"></i> Generate Key</a></li>
        <li><a href="{{ route('license-management.keys') }}"><i class="fa fa-list"></i> View Keys</a></li>
        <li><a href="{{ route('license-management.search') }}"><i class="fa fa-search"></i> Search Keys</a></li>
    </ul>
</li>
@endif