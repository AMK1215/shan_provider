<div class="form-row">
    <div class="form-group col-md-6">
        <label>Provider</label>
        <input type="text" name="provider" class="form-control" value="{{ old('provider', $product->provider ?? '') }}" required>
    </div>
    <div class="form-group col-md-6">
        <label>Currency</label>
        <input type="text" name="currency" class="form-control" value="{{ old('currency', $product->currency ?? '') }}" required>
    </div>
</div>
<div class="form-row">
    <div class="form-group col-md-6">
        <label>Status</label>
        <input type="text" name="status" class="form-control" value="{{ old('status', $product->status ?? '') }}" required>
    </div>
    <div class="form-group col-md-6">
        <label>Provider ID</label>
        <input type="number" name="provider_id" class="form-control" value="{{ old('provider_id', $product->provider_id ?? '') }}" required>
    </div>
</div>
<div class="form-row">
    <div class="form-group col-md-6">
        <label>Provider Product ID</label>
        <input type="number" name="provider_product_id" class="form-control" value="{{ old('provider_product_id', $product->provider_product_id ?? '') }}" required>
    </div>
    <div class="form-group col-md-6">
        <label>Product Code</label>
        <input type="text" name="product_code" class="form-control" value="{{ old('product_code', $product->product_code ?? '') }}" required>
    </div>
</div>
<div class="form-row">
    <div class="form-group col-md-6">
        <label>Product Name</label>
        <input type="text" name="product_name" class="form-control" value="{{ old('product_name', $product->product_name ?? '') }}" required>
    </div>
    <div class="form-group col-md-6">
        <label>Game Type</label>
        <input type="text" name="game_type" class="form-control" value="{{ old('game_type', $product->game_type ?? '') }}" required>
    </div>
</div>
<div class="form-row">
    <div class="form-group col-md-6">
        <label>Product Title</label>
        <input type="text" name="product_title" class="form-control" value="{{ old('product_title', $product->product_title ?? '') }}" required>
    </div>
    <div class="form-group col-md-6">
        <label>Short Name</label>
        <input type="text" name="short_name" class="form-control" value="{{ old('short_name', $product->short_name ?? '') }}">
    </div>
</div>
<div class="form-row">
    <div class="form-group col-md-6">
        <label>Order</label>
        <input type="number" name="order" class="form-control" value="{{ old('order', $product->order ?? 0) }}">
    </div>
    <div class="form-group col-md-6">
        <label>Game List Status</label>
        <select name="game_list_status" class="form-control">
            <option value="1" {{ old('game_list_status', $product->game_list_status ?? 1) == 1 ? 'selected' : '' }}>Active</option>
            <option value="0" {{ old('game_list_status', $product->game_list_status ?? 1) == 0 ? 'selected' : '' }}>Inactive</option>
        </select>
    </div>
</div>
<div class="form-row">
    <div class="form-group col-md-12">
        <label>Product Image</label>
        <input type="file" name="image" class="form-control-file">
        @if(isset($product) && $product->imgUrl)
            <small class="form-text text-muted">Leave blank to keep current image.</small>
        @endif
    </div>
</div> 