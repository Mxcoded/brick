<form action="{{ route('restaurant.select-source') }}" method="POST">
    @csrf
    <input type="hidden" name="type" value="{{ $type }}">
    <input type="hidden" name="source_id" value="{{ $source['id'] }}">
    <button type="submit" class="btn btn-primary w-100">Select {{ ucfirst($type) }}</button>
</form>