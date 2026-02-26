<form method="POST" action="{{ route('admin.tte.signing.dispatchBulk') }}" class="card shadow-sm border-0 rounded-4 mb-3">
  @csrf

  <div class="card-body">
    <div class="row g-3 align-items-end">

      <div class="col-md-4">
        <label class="form-label fw-semibold">Signer</label>
        <select name="signer_cert_code" class="form-select" required>
          <option value="">-- pilih signer --</option>
          @foreach($signers as $s)
            <option value="{{ $s->code }}">{{ $s->name }} ({{ $s->code }})</option>
          @endforeach
        </select>
      </div>

      <div class="col-md-2">
        <label class="form-label fw-semibold">Mode</label>
        <select name="appearance_mode" class="form-select" required>
          <option value="visible" selected>Visible</option>
          <option value="hidden">Hidden</option>
        </select>
      </div>

      <div class="col-md-2">
        <label class="form-label fw-semibold">Halaman</label>
        <input type="number" name="appearance_page" class="form-control" value="1" min="1">
      </div>

      <div class="col-md-1">
        <label class="form-label fw-semibold">X</label>
        <input type="number" name="appearance_x" class="form-control" value="30" min="0">
      </div>

      <div class="col-md-1">
        <label class="form-label fw-semibold">Y</label>
        <input type="number" name="appearance_y" class="form-control" value="30" min="0">
      </div>

      <div class="col-md-1">
        <label class="form-label fw-semibold">W</label>
        <input type="number" name="appearance_w" class="form-control" value="160" min="10">
      </div>

      <div class="col-md-1">
        <label class="form-label fw-semibold">H</label>
        <input type="number" name="appearance_h" class="form-control" value="50" min="10">
      </div>

      <div class="col-12 d-flex gap-2">
        <button class="btn btn-primary">
          Dispatch Sign All (max 20)
        </button>
      </div>

    </div>
  </div>
</form>