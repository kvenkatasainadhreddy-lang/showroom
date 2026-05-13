@extends('layouts.admin')
@section('title','Vehicle Catalogue Setup')

@push('styles')
<style>
/* ── All custom vars scoped to this page only ── */
.cat-page {
  --brand-accent:   #2563EB;
  --model-accent:   #059669;
  --variant-accent: #D97706;
  --cat-surface:    #F5F5F2;
  --cat-card:       #FFFFFF;
  --cat-border:     #E2E0DA;
  --cat-border-lt:  #EDEDEA;
  --cat-radius-sm:  5px;
  --cat-radius-md:  8px;
  --cat-radius-lg:  12px;
  --cat-shadow:     0 1px 3px rgba(0,0,0,0.07), 0 1px 2px rgba(0,0,0,0.04);
  background: var(--cat-surface);
  padding: 4px 0;
}

/* ── PAGE HEADER ── */
.cat-header {
  display: flex;
  align-items: flex-end;
  justify-content: space-between;
  margin-bottom: 20px;
  gap: 12px;
  flex-wrap: wrap;
}
.cat-header-eyebrow {
  font-size: 0.7rem;
  font-weight: 500;
  letter-spacing: 0.07em;
  text-transform: uppercase;
  color: #9CA3AF;
  margin-bottom: 2px;
}
.cat-header-title {
  font-size: 1.1rem;
  font-weight: 700;
  line-height: 1.2;
}
.cat-header-sub {
  font-size: 0.8rem;
  color: #6B7280;
  margin-top: 2px;
}
.btn-chassis {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 7px 14px;
  background: #111827;
  color: #fff;
  border-radius: var(--cat-radius-md);
  font-size: 0.8rem;
  font-weight: 500;
  text-decoration: none;
  transition: opacity 0.15s, transform 0.12s;
  white-space: nowrap;
}
.btn-chassis:hover { opacity: 0.85; transform: translateY(-1px); color: #fff; text-decoration: none; }
.btn-chassis svg { flex-shrink: 0; }

/* ── ALERT ── */
.cat-alert {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 9px 14px;
  border-radius: var(--cat-radius-md);
  font-size: 0.85rem;
  margin-bottom: 16px;
  animation: catSlideDown 0.2s ease;
}
.cat-alert-success { background: #ECFDF5; border: 1px solid #A7F3D0; color: #065F46; }
@keyframes catSlideDown { from { opacity:0; transform:translateY(-5px); } to { opacity:1; transform:translateY(0); } }

/* ── PROGRESS STEPS ── */
.cat-steps {
  display: flex;
  align-items: center;
  margin-bottom: 20px;
  background: var(--cat-card);
  border: 1px solid var(--cat-border);
  border-radius: var(--cat-radius-lg);
  padding: 12px 18px;
  box-shadow: var(--cat-shadow);
}
.cat-step {
  display: flex;
  align-items: center;
  gap: 8px;
  flex: 1;
}
.cat-step-num {
  width: 24px;
  height: 24px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.7rem;
  font-weight: 600;
  flex-shrink: 0;
}
.cat-step-num.step-brand   { background: #EFF6FF; color: var(--brand-accent);   border: 1.5px solid #BFDBFE; }
.cat-step-num.step-model   { background: #ECFDF5; color: var(--model-accent);   border: 1.5px solid #A7F3D0; }
.cat-step-num.step-variant { background: #FFFBEB; color: var(--variant-accent); border: 1.5px solid #FDE68A; }
.cat-step-label { font-size: 0.8rem; font-weight: 600; }
.cat-step-count { font-size: 0.72rem; color: #9CA3AF; }
.cat-step-arrow {
  width: 20px;
  height: 1px;
  background: var(--cat-border);
  margin: 0 6px;
  flex-shrink: 0;
  position: relative;
}
.cat-step-arrow::after {
  content: '';
  position: absolute;
  right: -4px;
  top: -3px;
  border: 3px solid transparent;
  border-left-color: var(--cat-border);
}

/* ── COLUMNS GRID ── */
.cat-cols {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 14px;
  align-items: start;
}

/* ── COLUMN CARD ── */
.cat-col {
  background: var(--cat-card);
  border: 1px solid var(--cat-border);
  border-radius: var(--cat-radius-lg);
  box-shadow: var(--cat-shadow);
  overflow: hidden;
  display: flex;
  flex-direction: column;
}
.cat-col-brand   { border-top: 3px solid var(--brand-accent); }
.cat-col-model   { border-top: 3px solid var(--model-accent); }
.cat-col-variant { border-top: 3px solid var(--variant-accent); }

/* ── COLUMN HEADER ── */
.cat-col-head {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 11px 14px;
  border-bottom: 1px solid var(--cat-border-lt);
}
.col-icon {
  width: 30px;
  height: 30px;
  border-radius: var(--cat-radius-sm);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}
.col-icon-brand   { background: #EFF6FF; color: var(--brand-accent); }
.col-icon-model   { background: #ECFDF5; color: var(--model-accent); }
.col-icon-variant { background: #FFFBEB; color: var(--variant-accent); }
.col-title { font-size: 0.85rem; font-weight: 700; flex: 1; }
.col-badge {
  font-size: 0.72rem;
  font-weight: 600;
  padding: 2px 8px;
  border-radius: 20px;
}
.col-badge-brand   { background: #EFF6FF; color: var(--brand-accent); }
.col-badge-model   { background: #ECFDF5; color: var(--model-accent); }
.col-badge-variant { background: #FFFBEB; color: var(--variant-accent); }

/* ── ADD FORM AREA ── */
.cat-add-area {
  padding: 12px 14px;
  border-bottom: 1px solid var(--cat-border-lt);
  background: #FAFAF9;
  display: flex;
  flex-direction: column;
  gap: 7px;
}
.cat-input-row { display: flex; gap: 5px; }
.cat-input {
  flex: 1;
  min-width: 0;
  height: 32px;
  padding: 0 9px;
  border: 1px solid var(--cat-border);
  border-radius: var(--cat-radius-sm);
  background: var(--cat-card);
  color: inherit;
  font-size: 0.8rem;
  font-family: inherit;
  transition: border-color 0.15s, box-shadow 0.15s;
}
.cat-input::placeholder { color: #C0BCB8; }
.cat-input:focus {
  outline: none;
  border-color: #93C5FD;
  box-shadow: 0 0 0 3px rgba(37,99,235,0.08);
}
.cat-select {
  width: 100%;
  height: 32px;
  padding: 0 28px 0 9px;
  border: 1px solid var(--cat-border);
  border-radius: var(--cat-radius-sm);
  background: var(--cat-card);
  color: inherit;
  font-size: 0.8rem;
  font-family: inherit;
  cursor: pointer;
  appearance: none;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath d='M2 4l4 4 4-4' stroke='%23C0BCB8' stroke-width='1.5' fill='none' stroke-linecap='round'/%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right 9px center;
  transition: border-color 0.15s;
}
.cat-select:focus { outline: none; border-color: #93C5FD; }

.cat-add-btn {
  height: 32px;
  padding: 0 13px;
  border: none;
  border-radius: var(--cat-radius-sm);
  font-size: 0.8rem;
  font-weight: 600;
  font-family: inherit;
  cursor: pointer;
  white-space: nowrap;
  flex-shrink: 0;
  transition: opacity 0.15s, transform 0.12s;
  display: inline-flex;
  align-items: center;
  gap: 5px;
}
.cat-add-btn:hover { opacity: 0.88; transform: translateY(-1px); }
.cat-add-btn:active { transform: translateY(0); }
.btn-brand   { background: var(--brand-accent);   color: #fff; }
.btn-model   { background: var(--model-accent);   color: #fff; }
.btn-variant { background: var(--variant-accent); color: #fff; }

.cat-err {
  font-size: 0.75rem;
  color: #DC2626;
  min-height: 15px;
}

/* ── FILTER BAR ── */
.cat-filter {
  padding: 8px 14px;
  border-bottom: 1px solid var(--cat-border-lt);
}
.cat-filter .cat-select {
  height: 28px;
  font-size: 0.77rem;
  background-color: #FAFAF9;
}

/* ── ITEM LIST ── */
.cat-list {
  list-style: none;
  overflow-y: auto;
  max-height: 400px;
}
.cat-list::-webkit-scrollbar { width: 4px; }
.cat-list::-webkit-scrollbar-track { background: transparent; }
.cat-list::-webkit-scrollbar-thumb { background: var(--cat-border); border-radius: 4px; }

.cat-item {
  display: flex;
  align-items: center;
  gap: 9px;
  padding: 9px 14px;
  border-bottom: 1px solid var(--cat-border-lt);
  cursor: pointer;
  transition: background 0.1s;
}
.cat-item:last-child { border-bottom: none; }
.cat-item:hover { background: #FAFAF9; }
.cat-item.active-brand { background: #EFF6FF; }
.cat-item.active-model { background: #ECFDF5; }

.item-dot {
  width: 7px;
  height: 7px;
  border-radius: 50%;
  flex-shrink: 0;
}
.dot-brand   { background: var(--brand-accent); }
.dot-model   { background: var(--model-accent); }
.dot-variant { background: var(--variant-accent); }

.item-info { flex: 1; min-width: 0; }
.item-name {
  font-size: 0.82rem;
  font-weight: 500;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  line-height: 1.3;
}
.item-meta {
  font-size: 0.75rem;
  color: #9CA3AF;
  margin-top: 1px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.item-color-dot {
  display: inline-block;
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background: #60A5FA;
  vertical-align: middle;
  margin-right: 3px;
}
.item-stat {
  font-size: 0.72rem;
  font-weight: 500;
  padding: 2px 7px;
  border-radius: 20px;
  white-space: nowrap;
  flex-shrink: 0;
}
.stat-models   { background: #EFF6FF; color: var(--brand-accent); }
.stat-variants { background: #ECFDF5; color: var(--model-accent); }
.stat-avail    { background: #ECFDF5; color: var(--model-accent); }
.stat-empty    { background: #F3F4F6; color: #9CA3AF; }

.item-new-badge {
  font-size: 0.68rem;
  font-weight: 600;
  padding: 2px 6px;
  border-radius: 20px;
  background: #ECFDF5;
  color: var(--model-accent);
  border: 1px solid #A7F3D0;
  flex-shrink: 0;
}
.item-edit {
  width: 26px;
  height: 26px;
  border-radius: var(--cat-radius-sm);
  border: 1px solid var(--cat-border);
  background: var(--cat-card);
  color: #9CA3AF;
  display: flex;
  align-items: center;
  justify-content: center;
  text-decoration: none;
  flex-shrink: 0;
  transition: background 0.12s, color 0.12s, border-color 0.12s;
}
.item-edit:hover { background: #F3F4F6; color: #374151; border-color: #D1D5DB; }

/* ── EMPTY STATE ── */
.cat-empty {
  padding: 28px 14px;
  text-align: center;
}
.cat-empty-icon {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  background: #F3F4F6;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 8px;
  color: #9CA3AF;
}
.cat-empty-text { font-size: 0.8rem; color: #9CA3AF; }

/* ── CTA STRIP ── */
.cat-cta {
  margin-top: 16px;
  background: var(--cat-card);
  border: 1px solid var(--cat-border);
  border-radius: var(--cat-radius-lg);
  padding: 12px 18px;
  display: flex;
  align-items: center;
  gap: 12px;
  box-shadow: var(--cat-shadow);
  flex-wrap: wrap;
}
.cta-icon {
  width: 34px;
  height: 34px;
  border-radius: var(--cat-radius-sm);
  background: #EFF6FF;
  color: var(--brand-accent);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}
.cta-body { flex: 1; }
.cta-title { font-size: 0.82rem; font-weight: 600; }
.cta-sub   { font-size: 0.77rem; color: #6B7280; margin-top: 2px; }
.btn-cta {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 7px 16px;
  background: var(--brand-accent);
  color: #fff;
  border-radius: var(--cat-radius-md);
  font-size: 0.8rem;
  font-weight: 500;
  font-family: inherit;
  text-decoration: none;
  transition: opacity 0.15s, transform 0.12s;
  white-space: nowrap;
}
.btn-cta:hover { opacity: 0.88; transform: translateY(-1px); color: #fff; text-decoration: none; }

/* spinner */
.cat-spinner {
  display: inline-block;
  width: 12px;
  height: 12px;
  border: 2px solid rgba(255,255,255,0.4);
  border-top-color: #fff;
  border-radius: 50%;
  animation: catSpin 0.6s linear infinite;
}
@keyframes catSpin { to { transform: rotate(360deg); } }

@media (max-width: 900px) {
  .cat-cols { grid-template-columns: 1fr; }
  .cat-steps { flex-wrap: wrap; gap: 8px; }
  .cat-step-arrow { display: none; }
}
</style>
@endpush

@section('content')

<div class="cat-page">

  {{-- ── PAGE HEADER ── --}}
  <div class="cat-header">
    <div class="cat-header-left">
      <div class="cat-header-eyebrow">Inventory Setup</div>
      <div class="cat-header-title">Vehicle Catalogue</div>
      <div class="cat-header-sub">Build your catalogue: Brands → Models → Variants</div>
    </div>
    <a href="{{ route('admin.catalogue.chassis') }}" class="btn-chassis">
      <svg width="15" height="15" viewBox="0 0 16 16" fill="none">
        <rect x="1" y="5" width="14" height="7" rx="2" stroke="currentColor" stroke-width="1.3"/>
        <circle cx="4.5" cy="12" r="1.5" fill="currentColor"/>
        <circle cx="11.5" cy="12" r="1.5" fill="currentColor"/>
        <path d="M5 5V4a3 3 0 0 1 6 0v1" stroke="currentColor" stroke-width="1.3"/>
      </svg>
      Chassis Registry
    </a>
  </div>

  {{-- ── SUCCESS ALERT ── --}}
  @if(session('success'))
  <div class="cat-alert cat-alert-success">
    <svg width="15" height="15" viewBox="0 0 16 16" fill="none"><circle cx="8" cy="8" r="6.5" stroke="#059669" stroke-width="1.3"/><path d="M5 8l2 2 4-4" stroke="#059669" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/></svg>
    {{ session('success') }}
  </div>
  @endif

  {{-- ── PROGRESS STEPS ── --}}
  <div class="cat-steps">
    <div class="cat-step">
      <div class="cat-step-num step-brand">1</div>
      <div>
        <div class="cat-step-label">Brands</div>
        <div class="cat-step-count">{{ $brands->count() }} added</div>
      </div>
    </div>
    <div class="cat-step-arrow"></div>
    <div class="cat-step">
      <div class="cat-step-num step-model">2</div>
      <div>
        <div class="cat-step-label">Models</div>
        <div class="cat-step-count">{{ $models->count() }} added</div>
      </div>
    </div>
    <div class="cat-step-arrow"></div>
    <div class="cat-step">
      <div class="cat-step-num step-variant">3</div>
      <div>
        <div class="cat-step-label">Variants</div>
        <div class="cat-step-count">{{ $variants->count() }} added</div>
      </div>
    </div>
    <div class="cat-step-arrow"></div>
    <div class="cat-step">
      <div class="cat-step-num" style="background:#F3F4F6;color:#9CA3AF;border:1.5px solid #E5E7EB;">4</div>
      <div>
        <div class="cat-step-label" style="color:#6B7280">Chassis</div>
        <div class="cat-step-count">assign VINs</div>
      </div>
    </div>
  </div>

  {{-- ── THREE COLUMNS ── --}}
  <div class="cat-cols">

    {{-- ═══ COLUMN 1 — BRANDS ═══ --}}
    <div class="cat-col cat-col-brand">
      <div class="cat-col-head">
        <div class="col-icon col-icon-brand">
          <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
            <path d="M8 2l1.5 3 3.3.5-2.4 2.3.6 3.3L8 9.5l-3 1.6.6-3.3L3.2 5.5l3.3-.5z" stroke="currentColor" stroke-width="1.3" stroke-linejoin="round"/>
          </svg>
        </div>
        <span class="col-title">Brands</span>
        <span class="col-badge col-badge-brand" id="brandCount">{{ $brands->count() }}</span>
      </div>

      <div class="cat-add-area">
        <div class="cat-input-row">
          <input type="text" id="newBrandName" class="cat-input" placeholder="Brand name e.g. Honda" style="flex:2">
          <input type="text" id="newBrandCountry" class="cat-input" placeholder="Country" style="flex:1;min-width:0">
        </div>
        <div class="cat-input-row">
          <button class="cat-add-btn btn-brand" id="saveBrandBtn" type="button" style="width:100%;justify-content:center">
            <svg width="12" height="12" viewBox="0 0 12 12" fill="none"><path d="M6 1v10M1 6h10" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
            Add Brand
          </button>
        </div>
        <div class="cat-err" id="brandError"></div>
      </div>

      <ul class="cat-list" id="brandList">
        @forelse($brands as $b)
        <li class="cat-item brand-item" data-id="{{ $b->id }}" data-name="{{ $b->name }}">
          <div class="item-dot dot-brand"></div>
          <div class="item-info">
            <div class="item-name">{{ $b->name }}</div>
            <div class="item-meta">{{ $b->country ?: 'No country set' }}</div>
          </div>
          <span class="item-stat stat-models">{{ $b->vehicle_models_count }} models</span>
          <a href="{{ route('admin.brands.edit', $b) }}" class="item-edit" onclick="event.stopPropagation()">
            <svg width="13" height="13" viewBox="0 0 16 16" fill="none"><path d="M11 2.5l2.5 2.5-8 8H3V10.5l8-8z" stroke="currentColor" stroke-width="1.3" stroke-linejoin="round"/></svg>
          </a>
        </li>
        @empty
        <li class="cat-empty">
          <div class="cat-empty-icon">
            <svg width="18" height="18" viewBox="0 0 16 16" fill="none"><path d="M8 2l1.5 3 3.3.5-2.4 2.3.6 3.3L8 9.5l-3 1.6.6-3.3L3.2 5.5l3.3-.5z" stroke="currentColor" stroke-width="1.3" stroke-linejoin="round"/></svg>
          </div>
          <div class="cat-empty-text">No brands yet — add one above</div>
        </li>
        @endforelse
      </ul>
    </div>

    {{-- ═══ COLUMN 2 — MODELS ═══ --}}
    <div class="cat-col cat-col-model">
      <div class="cat-col-head">
        <div class="col-icon col-icon-model">
          <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
            <rect x="1.5" y="5.5" width="13" height="7" rx="1.5" stroke="currentColor" stroke-width="1.3"/>
            <path d="M5 5.5V4.5a3 3 0 0 1 6 0v1" stroke="currentColor" stroke-width="1.3"/>
          </svg>
        </div>
        <span class="col-title">Models</span>
        <span class="col-badge col-badge-model" id="modelCount">{{ $models->count() }}</span>
      </div>

      <div class="cat-add-area">
        <select id="modelBrandSel" class="cat-select">
          <option value="">— Select a brand —</option>
          @foreach($brands as $b)
          <option value="{{ $b->id }}">{{ $b->name }}</option>
          @endforeach
        </select>
        <div class="cat-input-row">
          <input type="text" id="newModelName" class="cat-input" placeholder="Model name e.g. Activa 6G">
          <button class="cat-add-btn btn-model" id="saveModelBtn" type="button">
            <svg width="12" height="12" viewBox="0 0 12 12" fill="none"><path d="M6 1v10M1 6h10" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
            Add
          </button>
        </div>
        <div class="cat-err" id="modelError"></div>
      </div>

      <div class="cat-filter">
        <select id="modelFilterBrand" class="cat-select">
          <option value="">All brands</option>
          @foreach($brands as $b)
          <option value="{{ $b->id }}">{{ $b->name }}</option>
          @endforeach
        </select>
      </div>

      <ul class="cat-list" id="modelList">
        @forelse($models as $m)
        <li class="cat-item model-item" data-id="{{ $m->id }}" data-name="{{ $m->name }}" data-brand="{{ $m->brand_id }}">
          <div class="item-dot dot-model"></div>
          <div class="item-info">
            <div class="item-name">{{ $m->name }}</div>
            <div class="item-meta">{{ $m->brand?->name }}</div>
          </div>
          <span class="item-stat stat-variants">{{ $m->variants_count }} variants</span>
          <a href="{{ route('admin.vehicle-models.edit', $m) }}" class="item-edit" onclick="event.stopPropagation()">
            <svg width="13" height="13" viewBox="0 0 16 16" fill="none"><path d="M11 2.5l2.5 2.5-8 8H3V10.5l8-8z" stroke="currentColor" stroke-width="1.3" stroke-linejoin="round"/></svg>
          </a>
        </li>
        @empty
        <li class="cat-empty">
          <div class="cat-empty-icon">
            <svg width="18" height="18" viewBox="0 0 16 16" fill="none"><rect x="1.5" y="5.5" width="13" height="7" rx="1.5" stroke="currentColor" stroke-width="1.3"/><path d="M5 5.5V4.5a3 3 0 0 1 6 0v1" stroke="currentColor" stroke-width="1.3"/></svg>
          </div>
          <div class="cat-empty-text">No models yet — select a brand above</div>
        </li>
        @endforelse
      </ul>
    </div>

    {{-- ═══ COLUMN 3 — VARIANTS ═══ --}}
    <div class="cat-col cat-col-variant">
      <div class="cat-col-head">
        <div class="col-icon col-icon-variant">
          <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
            <circle cx="8" cy="8" r="5.5" stroke="currentColor" stroke-width="1.3"/>
            <circle cx="8" cy="8" r="2" fill="currentColor"/>
          </svg>
        </div>
        <span class="col-title">Variants</span>
        <span class="col-badge col-badge-variant" id="variantCount">{{ $variants->count() }}</span>
      </div>

      <div class="cat-add-area">
        <select id="variantModelSel" class="cat-select">
          <option value="">— Select a model —</option>
          @foreach($models as $m)
          <option value="{{ $m->id }}" data-brand="{{ $m->brand_id }}">{{ $m->brand?->name }} / {{ $m->name }}</option>
          @endforeach
        </select>
        <div class="cat-input-row">
          <input type="text" id="newVariantName" class="cat-input" placeholder="Variant name">
          <input type="text" id="newVariantColor" class="cat-input" placeholder="Color" style="max-width:90px">
        </div>
        <div class="cat-input-row">
          <button class="cat-add-btn btn-variant" id="saveVariantBtn" type="button" style="width:100%;justify-content:center">
            <svg width="12" height="12" viewBox="0 0 12 12" fill="none"><path d="M6 1v10M1 6h10" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
            Add Variant
          </button>
        </div>
        <div class="cat-err" id="variantError"></div>
      </div>

      <div class="cat-filter">
        <select id="variantFilterModel" class="cat-select">
          <option value="">All models</option>
          @foreach($models as $m)
          <option value="{{ $m->id }}">{{ $m->brand?->name }} / {{ $m->name }}</option>
          @endforeach
        </select>
      </div>

      <ul class="cat-list" id="variantList">
        @forelse($variants as $v)
        <li class="cat-item variant-item" data-id="{{ $v->id }}" data-name="{{ $v->name }}" data-model="{{ $v->model_id }}">
          <div class="item-dot dot-variant"></div>
          <div class="item-info">
            <div class="item-name">{{ $v->name }}</div>
            <div class="item-meta">
              {{ $v->vehicleModel?->brand?->name }} / {{ $v->vehicleModel?->name }}
              @if($v->color)
              · <span class="item-color-dot"></span>{{ $v->color }}
              @endif
            </div>
          </div>
          @if($v->available_count > 0)
            <span class="item-stat stat-avail">{{ $v->available_count }}/{{ $v->stock_count }}</span>
          @else
            <span class="item-stat stat-empty">{{ $v->available_count }}/{{ $v->stock_count }}</span>
          @endif
          <a href="{{ route('admin.vehicle-variants.edit', $v) }}" class="item-edit" onclick="event.stopPropagation()">
            <svg width="13" height="13" viewBox="0 0 16 16" fill="none"><path d="M11 2.5l2.5 2.5-8 8H3V10.5l8-8z" stroke="currentColor" stroke-width="1.3" stroke-linejoin="round"/></svg>
          </a>
        </li>
        @empty
        <li class="cat-empty">
          <div class="cat-empty-icon">
            <svg width="18" height="18" viewBox="0 0 16 16" fill="none"><circle cx="8" cy="8" r="5.5" stroke="currentColor" stroke-width="1.3"/><circle cx="8" cy="8" r="2" fill="currentColor"/></svg>
          </div>
          <div class="cat-empty-text">No variants yet — select a model above</div>
        </li>
        @endforelse
      </ul>
    </div>

  </div>{{-- end cat-cols --}}

  {{-- ── CTA STRIP ── --}}
  <div class="cat-cta">
    <div class="cta-icon">
      <svg width="18" height="18" viewBox="0 0 16 16" fill="none">
        <rect x="1" y="5" width="14" height="7" rx="2" stroke="currentColor" stroke-width="1.3"/>
        <circle cx="4.5" cy="12" r="1.5" fill="currentColor"/>
        <circle cx="11.5" cy="12" r="1.5" fill="currentColor"/>
        <path d="M5 5V4a3 3 0 0 1 6 0v1" stroke="currentColor" stroke-width="1.3"/>
      </svg>
    </div>
    <div class="cta-body">
      <div class="cta-title">Ready to assign chassis numbers?</div>
      <div class="cta-sub">Add VIN / chassis numbers to each variant to complete your inventory setup.</div>
    </div>
    <a href="{{ route('admin.catalogue.chassis') }}" class="btn-cta">
      Go to Chassis Registry
      <svg width="13" height="13" viewBox="0 0 16 16" fill="none"><path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/></svg>
    </a>
  </div>

</div>{{-- end cat-page --}}

@endsection

@push('scripts')
<script>
const CSRF = '{{ csrf_token() }}';

function setLoading(btnId, loading) {
  const btn = document.getElementById(btnId);
  if (loading) {
    btn.disabled = true;
    btn.dataset.orig = btn.innerHTML;
    btn.innerHTML = '<span class="cat-spinner"></span>';
  } else {
    btn.disabled = false;
    btn.innerHTML = btn.dataset.orig;
  }
}

function showErr(id, msg) { document.getElementById(id).textContent = msg; }
function clearErr(id)     { document.getElementById(id).textContent = ''; }

function appendToSelect(selId, value, text) {
  const opt = document.createElement('option');
  opt.value = value; opt.textContent = text;
  document.getElementById(selId).appendChild(opt);
}

// ════════════ ADD BRAND ════════════
document.getElementById('saveBrandBtn').addEventListener('click', function () {
  const name    = document.getElementById('newBrandName').value.trim();
  const country = document.getElementById('newBrandCountry').value.trim();
  clearErr('brandError');
  if (!name) { showErr('brandError', 'Brand name is required.'); return; }

  setLoading('saveBrandBtn', true);
  $.post('{{ route("admin.catalogue.brand.store") }}', { _token: CSRF, name, country })
    .done(res => {
      if (!res.success) return;
      appendToSelect('modelBrandSel',    res.brand.id, res.brand.name);
      appendToSelect('modelFilterBrand', res.brand.id, res.brand.name);

      const li = document.createElement('li');
      li.className = 'cat-item brand-item';
      li.dataset.id = res.brand.id; li.dataset.name = res.brand.name;
      li.innerHTML = `
        <div class="item-dot dot-brand"></div>
        <div class="item-info">
          <div class="item-name">${res.brand.name}</div>
          <div class="item-meta">${res.brand.country || 'No country set'}</div>
        </div>
        <span class="item-stat stat-models">0 models</span>
        <span class="item-new-badge">New</span>
        <a href="#" class="item-edit" onclick="event.stopPropagation()">
          <svg width="13" height="13" viewBox="0 0 16 16" fill="none"><path d="M11 2.5l2.5 2.5-8 8H3V10.5l8-8z" stroke="currentColor" stroke-width="1.3" stroke-linejoin="round"/></svg>
        </a>`;
      const empty = document.querySelector('#brandList .cat-empty');
      if (empty) empty.closest('li')?.remove();
      document.getElementById('brandList').appendChild(li);

      const bc = document.getElementById('brandCount');
      bc.textContent = parseInt(bc.textContent) + 1;
      document.getElementById('newBrandName').value = '';
      document.getElementById('newBrandCountry').value = '';
    })
    .fail(err => {
      const msg = err.responseJSON?.errors?.name?.[0] ?? 'Failed to add brand.';
      showErr('brandError', msg);
    })
    .always(() => setLoading('saveBrandBtn', false));
});

// ════════════ ADD MODEL ════════════
document.getElementById('saveModelBtn').addEventListener('click', function () {
  const brand_id = document.getElementById('modelBrandSel').value;
  const name     = document.getElementById('newModelName').value.trim();
  clearErr('modelError');
  if (!brand_id) { showErr('modelError', 'Select a brand first.'); return; }
  if (!name)     { showErr('modelError', 'Model name is required.'); return; }

  setLoading('saveModelBtn', true);
  $.post('{{ route("admin.catalogue.model.store") }}', { _token: CSRF, name, brand_id })
    .done(res => {
      if (!res.success) return;
      const label = `${res.model.brand_name} / ${res.model.name}`;
      const opt = document.createElement('option');
      opt.value = res.model.id; opt.textContent = label;
      opt.dataset.brand = res.model.brand_id;
      document.getElementById('variantModelSel').appendChild(opt.cloneNode(true));
      document.getElementById('variantFilterModel').appendChild(opt);

      const li = document.createElement('li');
      li.className = 'cat-item model-item';
      li.dataset.id = res.model.id; li.dataset.name = res.model.name; li.dataset.brand = res.model.brand_id;
      li.innerHTML = `
        <div class="item-dot dot-model"></div>
        <div class="item-info">
          <div class="item-name">${res.model.name}</div>
          <div class="item-meta">${res.model.brand_name}</div>
        </div>
        <span class="item-stat stat-variants">0 variants</span>
        <span class="item-new-badge">New</span>
        <a href="#" class="item-edit" onclick="event.stopPropagation()">
          <svg width="13" height="13" viewBox="0 0 16 16" fill="none"><path d="M11 2.5l2.5 2.5-8 8H3V10.5l8-8z" stroke="currentColor" stroke-width="1.3" stroke-linejoin="round"/></svg>
        </a>`;
      const empty = document.querySelector('#modelList .cat-empty');
      if (empty) empty.closest('li')?.remove();
      document.getElementById('modelList').appendChild(li);

      const mc = document.getElementById('modelCount');
      mc.textContent = parseInt(mc.textContent) + 1;
      document.getElementById('newModelName').value = '';
    })
    .fail(err => {
      const msg = err.responseJSON?.errors?.name?.[0] ?? err.responseJSON?.errors?.brand_id?.[0] ?? 'Failed to add model.';
      showErr('modelError', msg);
    })
    .always(() => setLoading('saveModelBtn', false));
});

// ════════════ ADD VARIANT ════════════
document.getElementById('saveVariantBtn').addEventListener('click', function () {
  const model_id  = document.getElementById('variantModelSel').value;
  const name      = document.getElementById('newVariantName').value.trim();
  const color     = document.getElementById('newVariantColor').value.trim();
  const modelName = document.getElementById('variantModelSel').selectedOptions[0]?.text || '';
  clearErr('variantError');
  if (!model_id) { showErr('variantError', 'Select a model first.'); return; }
  if (!name)     { showErr('variantError', 'Variant name is required.'); return; }

  setLoading('saveVariantBtn', true);
  $.post('{{ route("admin.catalogue.variant.store") }}', { _token: CSRF, name, model_id, color })
    .done(res => {
      if (!res.success) return;
      const li = document.createElement('li');
      li.className = 'cat-item variant-item';
      li.dataset.id = res.variant.id; li.dataset.model = res.variant.model_id;
      const colorHtml = res.variant.color
        ? ` · <span class="item-color-dot"></span>${res.variant.color}` : '';
      li.innerHTML = `
        <div class="item-dot dot-variant"></div>
        <div class="item-info">
          <div class="item-name">${res.variant.name}</div>
          <div class="item-meta">${res.variant.brand_name} / ${res.variant.model_name}${colorHtml}</div>
        </div>
        <span class="item-stat stat-empty">0/0</span>
        <span class="item-new-badge">New</span>
        <a href="#" class="item-edit" onclick="event.stopPropagation()">
          <svg width="13" height="13" viewBox="0 0 16 16" fill="none"><path d="M11 2.5l2.5 2.5-8 8H3V10.5l8-8z" stroke="currentColor" stroke-width="1.3" stroke-linejoin="round"/></svg>
        </a>`;
      const empty = document.querySelector('#variantList .cat-empty');
      if (empty) empty.closest('li')?.remove();
      document.getElementById('variantList').appendChild(li);

      const vc = document.getElementById('variantCount');
      vc.textContent = parseInt(vc.textContent) + 1;
      document.getElementById('newVariantName').value = '';
      document.getElementById('newVariantColor').value = '';
    })
    .fail(err => {
      const msg = err.responseJSON?.errors?.name?.[0] ?? 'Failed to add variant.';
      showErr('variantError', msg);
    })
    .always(() => setLoading('saveVariantBtn', false));
});

// ════════════ FILTER: Models by Brand ════════════
document.getElementById('modelFilterBrand').addEventListener('change', function () {
  const v = this.value;
  document.querySelectorAll('.model-item').forEach(el => {
    el.style.display = (!v || el.dataset.brand == v) ? '' : 'none';
  });
});

// ════════════ FILTER: Variants by Model ════════════
document.getElementById('variantFilterModel').addEventListener('change', function () {
  const v = this.value;
  document.querySelectorAll('.variant-item').forEach(el => {
    el.style.display = (!v || el.dataset.model == v) ? '' : 'none';
  });
});

// ════════════ CLICK BRAND → pre-fill model selects ════════════
document.addEventListener('click', function (e) {
  const brandItem = e.target.closest('.brand-item');
  const modelItem = e.target.closest('.model-item');

  if (brandItem && !e.target.closest('.item-edit')) {
    document.querySelectorAll('.brand-item').forEach(el => el.classList.remove('active-brand'));
    brandItem.classList.add('active-brand');
    const id = brandItem.dataset.id;
    document.getElementById('modelBrandSel').value = id;
    const mf = document.getElementById('modelFilterBrand');
    mf.value = id; mf.dispatchEvent(new Event('change'));
  }

  if (modelItem && !e.target.closest('.item-edit')) {
    document.querySelectorAll('.model-item').forEach(el => el.classList.remove('active-model'));
    modelItem.classList.add('active-model');
    const id = modelItem.dataset.id;
    document.getElementById('variantModelSel').value = id;
    const vf = document.getElementById('variantFilterModel');
    vf.value = id; vf.dispatchEvent(new Event('change'));
  }
});
</script>
@endpush