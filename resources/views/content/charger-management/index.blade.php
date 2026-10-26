@extends('layouts/contentNavbarLayout')

@section('title', 'Station Chargers')

@section('content')
<div class="row">
  <div class="col-md-12">

    {{-- Flash messages --}}
    @if (session('success'))
      <div class="alert alert-success alert-dismissible" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    @endif

    {{-- Station selector --}}
    <div class="card mb-6">
      <div class="card-header">
        <h5 class="mb-0">Station Chargers</h5>
        <small class="text-body">Choose a station to manage its chargers</small>
      </div>
      <div class="card-body">
        <form method="GET" action="{{ route('chargers.index') }}" class="row g-4 align-items-end">
          <div class="col-md-5">
            <label for="stationSelect" class="form-label">Station</label>
            <select id="stationSelect" name="station_id" class="form-select" onchange="this.form.submit()">
              <option value="">Select a station</option>
              @foreach ($stations as $station)
                <option value="{{ $station->id }}" {{ optional($selectedStation)->id === $station->id ? 'selected' : '' }}>
                  {{ $station->name }}
                </option>
              @endforeach
            </select>
          </div>
          <div class="col-md-auto">
            <button type="submit" class="btn btn-primary">View chargers</button>
          </div>
          @if ($selectedStation)
            @php
              $stationStatus = $selectedStation->status;
              $stationStatus = $stationStatus instanceof \BackedEnum
                ? $stationStatus->value
                : (string) $stationStatus;
              $stationColors = ['active' => 'success', 'inactive' => 'secondary', 'maintenance' => 'warning'];
              $stationColor = $stationColors[strtolower($stationStatus)] ?? 'secondary';
            @endphp
            <div class="col-md-auto">
              <span class="badge bg-label-{{ $stationColor }} me-2">{{ ucfirst(strtolower($stationStatus)) }}</span>
              <small class="text-body">{{ $selectedStation->location }}</small>
            </div>
          @endif
        </form>
      </div>
    </div>

    @if ($selectedStation)
      {{-- Chargers of the selected station --}}
      <div class="card mb-6">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-4">
          <div>
            <h5 class="mb-0">{{ $selectedStation->name }} &mdash; Chargers</h5>
            <small class="text-body">{{ $selectedStation->chargers->count() }} charger(s) at this station</small>
          </div>
          @can('chargers.create')
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#attachChargerModal">
              <i class="icon-base bx bx-plus me-1"></i> Add Chargers
            </button>
          @endcan
        </div>

        <div class="table-responsive text-nowrap">
          <table class="table">
            <thead>
              <tr>
                <th>Serial Number</th>
                <th>Manufacturer</th>
                <th>Model</th>
                <th>Firmware</th>
                <th>Power (kW)</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody class="table-border-bottom-0">
              @forelse ($selectedStation->chargers as $charger)
                @php
                  $chargerStatus = $charger->status instanceof \BackedEnum
                    ? $charger->status->value
                    : (string) $charger->status;
                  $chargerColors = ['available' => 'success', 'reserved' => 'info', 'charging completed' => 'primary', 'out of service' => 'secondary'];
                  $chargerColor = $chargerColors[strtolower($chargerStatus)] ?? 'secondary';
                @endphp
                <tr>
                  <td class="fw-medium">{{ $charger->serial_number }}</td>
                  <td>{{ $charger->manufacturer }}</td>
                  <td>{{ $charger->model }}</td>
                  <td>{{ $charger->firmware_version }}</td>
                  <td>{{ $charger->power_kw }}</td>
                  <td><span class="badge bg-label-{{ $chargerColor }}">{{ $chargerStatus }}</span></td>
                  <td>
                    <div class="dropdown">
                      <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                        <i class="icon-base bx bx-dots-vertical-rounded"></i>
                      </button>
                      <div class="dropdown-menu">
                        @can('chargers.update')
                          <a class="dropdown-item" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#editChargerModal{{ $charger->id }}">
                            <i class="icon-base bx bx-edit-alt me-1"></i> Edit
                          </a>
                          <a class="dropdown-item" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#addConnectorModal{{ $charger->id }}">
                            <i class="icon-base bx bx-plug me-1"></i> Add connector
                          </a>
                          <a class="dropdown-item" href="javascript:void(0);" onclick="document.getElementById('detachChargerForm{{ $charger->id }}').submit();">
                            <i class="icon-base bx bx-unlink me-1"></i> Remove from station
                          </a>
                        @endcan
                      </div>
                    </div>

                    {{-- Hidden detach form (keeps the charger in the database, unassigned) --}}
                    <form id="detachChargerForm{{ $charger->id }}" action="{{ route('chargers.detach', $charger->id) }}" method="POST" class="d-none" onsubmit="return confirm('Remove {{ $charger->serial_number }} from this station? It will stay in the database as unassigned.');">
                      @csrf
                      @method('PATCH')
                      <input type="hidden" name="station_id" value="{{ $selectedStation->id }}">
                    </form>

                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="7" class="text-center py-6">No chargers at this station yet.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>

      {{-- Edit Charger Modals --}}
      @foreach ($selectedStation->chargers as $charger)
        @php
          $chargerStatus = $charger->status instanceof \BackedEnum
            ? $charger->status->value
            : (string) $charger->status;
          $chargerColors = ['available' => 'success', 'reserved' => 'info', 'charging completed' => 'primary', 'out of service' => 'secondary'];
          $chargerColor = $chargerColors[strtolower($chargerStatus)] ?? 'secondary';
        @endphp
        <div class="modal fade" id="editChargerModal{{ $charger->id }}" tabindex="-1" aria-hidden="true">
          <div class="modal-dialog" role="document">
            <div class="modal-content">
              <form method="POST" action="{{ route('chargers.update', $charger->id) }}">
                @csrf
                @method('PUT')
                <div class="modal-header">
                  <h5 class="modal-title">Edit Charger</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                  <div class="mb-6">
                    <label class="form-label">Status</label>
                    <div>
                      <span class="badge bg-label-{{ $chargerColor }}">{{ $chargerStatus }}</span>
                    </div>
                  </div>
                  <div class="mb-6">
                    <label for="overstayFee{{ $charger->id }}" class="form-label">Default Overstay Fee</label>
                    <input type="number" step="0.01" min="0" id="overstayFee{{ $charger->id }}" name="default_overstay_fee_amount" class="form-control" value="{{ $charger->default_overstay_fee_amount }}" required />
                  </div>
                  <div class="mb-0">
                    <label for="overstayInterval{{ $charger->id }}" class="form-label">Default Overstay Interval (minutes)</label>
                    <input type="number" min="0" max="65535" id="overstayInterval{{ $charger->id }}" name="default_overstay_interval_minutes" class="form-control" value="{{ $charger->default_overstay_interval_minutes }}" required />
                  </div>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Close</button>
                  @if (strtolower($chargerStatus) === 'out of service')
                    <button type="submit" form="availableForm{{ $charger->id }}" class="btn btn-label-success">
                      Mark as available
                    </button>
                  @else
                    <button type="submit" form="outOfServiceForm{{ $charger->id }}" class="btn btn-label-danger">
                      Mark out of service
                    </button>
                  @endif
                  <button type="submit" class="btn btn-primary">Save changes</button>
                </div>
              </form>
              <form id="outOfServiceForm{{ $charger->id }}" method="POST" action="{{ route('chargers.out-of-service', $charger->id) }}" class="d-none" onsubmit="return confirm('Mark {{ $charger->serial_number }} as out of service?');">
                @csrf
                @method('PATCH')
              </form>
              <form id="availableForm{{ $charger->id }}" method="POST" action="{{ route('chargers.available', $charger->id) }}" class="d-none" onsubmit="return confirm('Mark {{ $charger->serial_number }} as available?');">
                @csrf
                @method('PATCH')
              </form>
            </div>
          </div>
        </div>

        <div class="modal fade" id="addConnectorModal{{ $charger->id }}" tabindex="-1" aria-hidden="true">
          <div class="modal-dialog" role="document">
            <div class="modal-content">
              <form method="POST" action="{{ route('chargers.connectors.store', $charger->id) }}">
                @csrf
                <div class="modal-header">
                  <h5 class="modal-title">Add Connector to {{ $charger->serial_number }}</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                  @if ($charger->connectors->isNotEmpty())
                    <div class="mb-5">
                      <label class="form-label">Existing connectors</label>
                      <ul class="list-group">
                        @foreach ($charger->connectors as $connector)
                          @php
                            $connectorStatus = $connector->status instanceof \BackedEnum
                              ? $connector->status->value
                              : (string) $connector->status;
                            $connectorColor = strtolower($connectorStatus) === 'available' ? 'success' : 'secondary';
                          @endphp
                          <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span>
                              #{{ $connector->number }} &middot;
                              {{ $connector->code }} ({{ $connector->current_type }})
                              <span class="badge bg-label-{{ $connectorColor }}">{{ $connectorStatus }}</span>
                            </span>
                            <span class="d-flex align-items-center gap-2">
                              {{ $connector->max_power_kw }} kW
                              <button type="button" class="btn btn-sm btn-icon btn-text-secondary" data-bs-toggle="modal" data-bs-target="#editConnectorModal{{ $connector->id }}" aria-label="Edit connector">
                                <i class="icon-base bx bx-edit-alt"></i>
                              </button>
                            </span>
                          </li>
                        @endforeach
                      </ul>
                    </div>
                  @endif
                  <div class="mb-5">
                    <label for="connector{{ $charger->id }}" class="form-label">Connector inventory</label>
                    <select id="connector{{ $charger->id }}" name="connector_id" class="form-select" required>
                      <option value="">Select a connector</option>
                      @foreach ($availableConnectors->where('current_type', $charger->current_type) as $availableConnector)
                        <option value="{{ $availableConnector->id }}" @selected(old('connector_id') == $availableConnector->id)>
                          {{ $availableConnector->code }} ({{ $availableConnector->current_type }}, {{ $availableConnector->max_power_kw }} kW)
                        </option>
                      @endforeach
                    </select>
                  </div>
                  <div class="mb-5">
                    <label for="connectorNumber{{ $charger->id }}" class="form-label">Connector number (1-3)</label>
                    <input type="number" min="1" max="3" id="connectorNumber{{ $charger->id }}" name="number" class="form-control" value="{{ old('number') }}" required />
                  </div>
                  @if ($errors->any())
                    <div class="alert alert-danger mt-5 mb-0">
                      <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                          <li>{{ $error }}</li>
                        @endforeach
                      </ul>
                    </div>
                  @endif
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Close</button>
                  <button type="submit" class="btn btn-primary">Add connector</button>
                </div>
              </form>
            </div>
          </div>
        </div>
      @endforeach

      @foreach ($selectedStation->chargers as $charger)
        @foreach ($charger->connectors as $connector)
          @php
            $connectorStatus = $connector->status instanceof \BackedEnum
              ? $connector->status->value
              : (string) $connector->status;
            $connectorColor = strtolower($connectorStatus) === 'available' ? 'success' : 'secondary';
          @endphp
          <div class="modal fade" id="editConnectorModal{{ $connector->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog" role="document">
              <div class="modal-content">
                <form method="POST" action="{{ route('connectors.update', $connector->id) }}">
                  @csrf
                  @method('PUT')
                  <div class="modal-header">
                    <h5 class="modal-title">Edit Connector</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                    <div class="mb-5">
                      <label for="connectorCodeEdit{{ $connector->id }}" class="form-label">Connector code</label>
                      <select id="connectorCodeEdit{{ $connector->id }}" name="code" class="form-select" required>
                        <option value="">Select connector code</option>
                        @foreach ($connectorInventory->where('current_type', $charger->current_type)->pluck('code')->unique() as $connectorCode)
                          <option value="{{ $connectorCode }}" @selected($connector->code === $connectorCode)>{{ $connectorCode }}</option>
                        @endforeach
                      </select>
                    </div>
                    <div class="mb-5">
                      <label class="form-label">Current type</label>
                      <input type="text" class="form-control" value="DC" readonly />
                    </div>
                    <div class="mb-5">
                      <label for="connectorNumberEdit{{ $connector->id }}" class="form-label">Connector number (1-3)</label>
                      <input type="number" min="1" max="3" id="connectorNumberEdit{{ $connector->id }}" name="number" class="form-control" value="{{ $connector->number }}" required />
                    </div>
                    <div class="mb-0">
                      <label for="connectorPowerEdit{{ $connector->id }}" class="form-label">Maximum power (kW)</label>
                      <select id="connectorPowerEdit{{ $connector->id }}" name="max_power_kw" class="form-select" required>
                        <option value="">Select maximum power</option>
                        @foreach ($connectorInventory->where('current_type', $charger->current_type)->pluck('max_power_kw')->unique()->sort() as $connectorPower)
                          <option value="{{ $connectorPower }}" @selected((float) $connector->max_power_kw === (float) $connectorPower)>{{ $connectorPower }} kW</option>
                        @endforeach
                      </select>
                    </div>
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Close</button>
                    @if (strtolower($connectorStatus) === 'out of service')
                      <button type="submit" form="connectorAvailableForm{{ $connector->id }}" class="btn btn-label-success">Mark available</button>
                    @else
                      <button type="submit" form="connectorOutOfServiceForm{{ $connector->id }}" class="btn btn-label-danger">Mark out of service</button>
                    @endif
                    <button type="submit" class="btn btn-primary">Save changes</button>
                  </div>
                </form>
                <form id="connectorOutOfServiceForm{{ $connector->id }}" method="POST" action="{{ route('connectors.out-of-service', $connector->id) }}" class="d-none" onsubmit="return confirm('Mark this connector as out of service?');">
                  @csrf
                  @method('PATCH')
                </form>
                <form id="connectorAvailableForm{{ $connector->id }}" method="POST" action="{{ route('connectors.available', $connector->id) }}" class="d-none">
                  @csrf
                  @method('PATCH')
                </form>
              </div>
            </div>
          </div>
        @endforeach
      @endforeach

      {{-- Add (attach) Chargers Modal: pick from unassigned chargers already in the database --}}
      <div class="modal fade" id="attachChargerModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog" role="document">
          <div class="modal-content">
            <form method="POST" action="{{ route('chargers.attach') }}">
              @csrf
              <input type="hidden" name="station_id" value="{{ $selectedStation->id }}" />
              <div class="modal-header">
                <h5 class="modal-title">Add Chargers to {{ $selectedStation->name }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body">
                @forelse ($availableChargers as $available)
                  <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" name="charger_ids[]" value="{{ $available->id }}" id="attachCharger{{ $available->id }}" />
                    <label class="form-check-label" for="attachCharger{{ $available->id }}">
                      {{ $available->serial_number }}
                      <small class="text-body">&middot; {{ $available->manufacturer }} {{ $available->model }}</small>
                    </label>
                  </div>
                @empty
                  <p class="mb-0 text-body">No unassigned chargers are available in the database.</p>
                @endforelse
                @error('charger_ids')
                  <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Close</button>
                @if ($availableChargers->isNotEmpty())
                  <button type="submit" class="btn btn-primary">Add selected</button>
                @endif
              </div>
            </form>
          </div>
        </div>
      </div>
    @endif

  </div>
</div>
@endsection