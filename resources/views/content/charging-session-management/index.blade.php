@extends('layouts/contentNavbarLayout')

@section('title', 'Charging Sessions')

@section('content')
<div class="row">
  <div class="col-md-12">
    @if (session('success'))
      <div class="alert alert-success alert-dismissible" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    @endif

    @if ($errors->any())
      <div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>
    @endif

    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <div>
          <h5 class="mb-0">Charging Sessions</h5>
          <small class="text-body">Manage arrivals, charging, and cancellations</small>
        </div>
        <form method="GET" class="d-flex gap-2">
          <select name="status" class="form-select" onchange="this.form.submit()">
            <option value="">All statuses</option>
            @foreach (['Confirmed', 'Cancelled', 'No Show', 'Completed'] as $status)
              <option value="{{ $status }}" @selected(request('status') === $status)>{{ $status }}</option>
            @endforeach
          </select>
        </form>
      </div>

      <div class="table-responsive text-nowrap">
        <table class="table">
          <thead>
            <tr>
              <th>Reservation</th>
              <th>User</th>
              <th>Station / Charger</th>
              <th>Schedule</th>
              <th>Status</th>
              <th>Charging</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody class="table-border-bottom-0">
            @forelse ($reservations as $reservation)
              @php
                $status = $reservation->status?->value ?? (string) $reservation->status;
                $color = match ($status) {
                  'Confirmed' => 'info',
                  'Completed' => 'success',
                  'Cancelled', 'No Show' => 'danger',
                  default => 'secondary',
                };
                $session = $reservation->chargingSession;
                $sessionStatus = $session?->charging_status?->value ?? 'Not started';
              @endphp
              <tr>
                <td class="fw-medium">#{{ $reservation->id }}</td>
                <td>{{ $reservation->user?->full_name ?? '—' }}</td>
                <td>
                  {{ $reservation->connector?->charger?->station?->name ?? '—' }}<br>
                  <small class="text-body">{{ $reservation->connector?->charger?->serial_number ?? '—' }}</small>
                </td>
                <td>
                  {{ $reservation->date }}<br>
                  <small class="text-body">{{ $reservation->start_time }} - {{ $reservation->arrival_deadline }}</small>
                </td>
                <td><span class="badge bg-label-{{ $color }}">{{ $status }}</span></td>
                <td>
                  <span class="badge bg-label-{{ $session ? 'primary' : 'secondary' }}">{{ $sessionStatus }}</span>
                  @if ($session)
                    <br><small>{{ $session->current_SOC }}% SOC</small>
                  @endif
                </td>
                <td>
                  <div class="d-flex gap-2">
                    @if ($status === 'Confirmed' && ! $session)
                      <form method="POST" action="{{ route('charging-sessions.start', $reservation) }}">
                        @csrf
                        <button class="btn btn-sm btn-primary" type="submit">Start</button>
                      </form>
                      <form method="POST" action="{{ route('charging-sessions.cancel', $reservation) }}" onsubmit="return confirm('Cancel this reservation?');">
                        @csrf
                        <button class="btn btn-sm btn-outline-danger" type="submit">Cancel</button>
                      </form>
                    @endif
                    @if ($session?->charging_status?->value === 'In Progress')
                      <form method="POST" action="{{ route('charging-sessions.stop', $reservation) }}" onsubmit="return confirm('Stop this charging session?');">
                        @csrf
                        <button class="btn btn-sm btn-warning" type="submit">Stop</button>
                      </form>
                    @endif
                  </div>
                </td>
              </tr>
            @empty
              <tr><td colspan="7" class="text-center py-6">No reservations found.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
      <div class="card-footer">{{ $reservations->links() }}</div>
    </div>
  </div>
</div>
@endsection
