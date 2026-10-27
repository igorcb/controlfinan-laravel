@if ($errors)
  <div class="rounded-md bg-red-50 px-4 py-3 text-sm text-red-800">
    <ul class="list-inside list-disc">
      @foreach ($errors as $message)<li>{{ $message }}</li>@endforeach
    </ul>
  </div>
@endif
