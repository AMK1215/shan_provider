@extends('admin_layouts.app')
@section('styles')
<style>
.transparent-btn {
 background: none;
 border: none;
 padding: 0;
 outline: none;
 cursor: pointer;
 box-shadow: none;
 appearance: none;
 /* For some browsers */
}
</style>
@endsection
@section('content')
<div class="row mt-4">
 <div class="col-12">
  <div class="card">
   <!-- Card header -->
   <div class="card-header pb-0">
    <div class="d-lg-flex">
     <div>
      <h5 class="mb-0">Game List Dashboards
        <span>
            <p>
                All Total Running Games on Site : {{ count($games) }}
            </p>
        </span>
      </h5>

     </div>
     <div class="ms-auto my-auto mt-lg-0 mt-4">
      <div class="ms-auto my-auto">

      </div>
     </div>
    </div>
   </div>
   <div class="table-responsive">
    @can('admin_access')
    <table class="table table-flush" id="users-search">
    <thead>
    <tr>
        <th class="bg-primary text-white">#</th>
        <th class="bg-success text-white">Game Type</th>
        <th class="bg-danger text-white">Product</th>
        <th class="bg-info text-white">Game Name</th>
        <th class="bg-warning text-white">Image</th>
        <th class="bg-success text-white">Game Status</th>
        <th class="bg-info text-white">HotGameStatus</th>
        <th class="bg-primary text-white">Actions</th>
    </tr>
</thead>
<tbody>
    @foreach($games as $index => $game)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ $game->gameType->name ?? 'N/A' }}</td>
            <td>{{ $game->product->name ?? 'N/A' }}</td>
            <td>{{ $game->name }}</td>
            <td>
                <img src="{{ $game->image_url }}" alt="{{ $game->name }}" width="100px">
            </td>
            <td>
                @if($game->status == 1)
                <p>Running Game</p>
                @else
                <p>Game is Closed</p>
                @endif
            </td>
            <td>
                @if($game->hot_status == 1)
                <p>This Game is Hot</p>
                @else
                <p>Game is Normal</p>
                @endif
            </td>
            <td>
                {{-- <a href="{{ route('admin.gameLists.edit', $game->id) }}" class="btn btn-info btn-sm">Edit</a> --}}
                 <form action="{{ route('admin.gameLists.toggleStatus', $game->id) }}" method="POST" style="display:inline;">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn btn-warning btn-sm">
                        GameStatus
                    </button>
                </form>
                <form action="{{ route('admin.HotGame.toggleStatus', $game->id) }}" method="POST" style="display:inline;">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn btn-success btn-sm">
                        HotGame
                    </button>
                </form>
            </td>
        </tr>
    @endforeach
</tbody>
</table>
 {{ $games->links() }}
@endcan
   </div>

   <div class="card mt-4">
    <div class="card-body">



    </div>
   </div>
  </div>
 </div>
</div>
@endsection
@section('scripts')
<script src="{{ asset('admin_app/assets/js/plugins/datatables.js') }}"></script>
<script>
if (document.getElementById('users-search')) {
 const dataTableSearch = new simpleDatatables.DataTable("#users-search", {
  searchable: true,
  fixedHeight: false,
  perPage: 7
 });

 document.querySelectorAll(".export").forEach(function(el) {
  el.addEventListener("click", function(e) {
   var type = el.dataset.type;

   var data = {
    type: type,
    filename: "material-" + type,
   };

   if (type === "csv") {
    data.columnDelimiter = "|";
   }

   dataTableSearch.export(data);
  });
 });
};
</script>
<script>
var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
 return new bootstrap.Tooltip(tooltipTriggerEl)
})
</script>
@endsection
