<x-app-layout heading="Platform reports">
    <div class="grid grid-2">
        <div class="card">
            <div class="card-h">Recent member payments</div>
            <div class="table-wrap"><table>
                <tbody>
                @foreach($recentPayments as $payment)
                    <tr>
                        <td>{{ $payment->business?->name }}</td>
                        <td>{{ $payment->member?->name }}</td>
                        <td>{{ format_inr($payment->amount) }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table></div>
        </div>
        <div class="card">
            <div class="card-h">Expiring memberships</div>
            <div class="table-wrap"><table>
                <tbody>
                @foreach($expiring as $subscription)
                    <tr>
                        <td>{{ $subscription->business?->name }}</td>
                        <td>{{ $subscription->member?->name }}</td>
                        <td>{{ $subscription->ends_at?->toDateString() }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table></div>
        </div>
    </div>
</x-app-layout>
