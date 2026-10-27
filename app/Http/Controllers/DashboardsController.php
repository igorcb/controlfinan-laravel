<?php

namespace App\Http\Controllers;

use App\Models\CurrentAccount;
use App\Support\Auth;
use App\Support\Format;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;

class DashboardsController extends Controller
{
    public function show(Request $request)
    {
        $month = $this->parseMonth($request->query('month'));
        $monthAccounts = CurrentAccount::where('user_id', Auth::user()->id)->inMonth($month);

        $balanceByDay = CurrentAccount::balanceByDay($monthAccounts);
        $monthBalance = array_sum(array_map('floatval', $balanceByDay));
        $rideExpenses = CurrentAccount::totalByCost($monthAccounts, array_keys(CurrentAccount::RIDE_EXPENSES));
        $ridesBalance = (float) CurrentAccount::balanceOf((clone $monthAccounts)->rides());
        $ridesNet = $ridesBalance - array_sum(array_map('floatval', $rideExpenses));

        $monthlyRows = [['Corridas', $ridesBalance]];
        foreach (CurrentAccount::RIDE_EXPENSES as $costId => $name) {
            $monthlyRows[] = [$name, -(float) ($rideExpenses[$costId] ?? 0)];
        }
        $dailyRows = [];
        foreach ($balanceByDay as $day => $amount) {
            $dailyRows[] = [CarbonImmutable::parse($day)->format('d/m/Y'), (float) $amount];
        }
        $label = Format::MONTHS[$month->month - 1].' de '.$month->year;

        return view('dashboards.show', [
            'nav' => 'dashboard',
            'monthLabel' => mb_strtoupper(mb_substr($label, 0, 1)).mb_substr($label, 1),
            'previousMonth' => $month->subMonthNoOverflow()->format('Y-m'),
            'nextMonth' => $month->addMonthNoOverflow()->format('Y-m'),
            'today' => CarbonImmutable::today()->toDateString(),
            'dailyRows' => $dailyRows,
            'monthBalance' => $monthBalance,
            'monthlyRows' => $monthlyRows,
            'ridesNet' => $ridesNet,
        ]);
    }

    private function parseMonth(mixed $value): CarbonImmutable
    {
        if (is_string($value) && preg_match('/^(\d{4})-(\d{1,2})$/', $value, $m) && $m[2] >= 1 && $m[2] <= 12) {
            return CarbonImmutable::create((int) $m[1], (int) $m[2], 1);
        }

        return CarbonImmutable::today()->startOfMonth();
    }
}
