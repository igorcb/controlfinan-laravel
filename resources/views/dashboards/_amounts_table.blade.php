@php($F = \App\Support\Format::class)
<table class="min-w-full divide-y divide-slate-200 text-sm">
  <thead class="text-left text-xs font-medium uppercase tracking-wide text-slate-500">
    <tr>
      <th class="px-2 py-2 sm:px-4">{{ $header }}</th>
      <th class="px-2 py-2 sm:px-4 text-right">Valor</th>
    </tr>
  </thead>
  <tbody class="divide-y divide-slate-100">
    @foreach ($rows as [$label, $amount])
      <tr>
        <td class="px-2 py-2 sm:px-4">{{ $label }}</td>
        <td class="px-2 py-2 sm:px-4 text-right {{ $F::amountClass($amount) }}">{{ $F::currency($amount) }}</td>
      </tr>
    @endforeach
    @if (empty($rows))
      <tr><td colspan="2" class="px-4 py-4 text-center text-slate-500">Nenhum lançamento no período.</td></tr>
    @endif
  </tbody>
  <tfoot>
    <tr class="border-t-2 border-slate-200 font-semibold">
      <td class="px-2 py-2 sm:px-4">{{ $totalLabel }}</td>
      <td class="px-2 py-2 sm:px-4 text-right {{ $F::amountClass($total) }}">{{ $F::currency($total) }}</td>
    </tr>
  </tfoot>
</table>
