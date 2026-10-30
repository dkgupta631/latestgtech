<div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box">
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="/">GTech</a></li>
                            <li class="breadcrumb-item active">{{ __('messages.All QR Lists') }}</li>
                        </ol>
                    </div>
                    <h4 class="page-title">{{ __('messages.All QR Lists') }}</h4>
                </div>
            </div>
        </div>     
        <!-- end page title --> 
        
          <div class="row">
                            <div class="col-12">
                                <div class="card-box">
                                    <div class="table-responsive mm-table-scroll">
                                        <table id="datatable-buttons" class="table table-striped table-bordered dt-responsive nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                                            <thead>
                                            <tr>
                                                <th>{{ __('messages.Order Id') }}</th>
                                                 <th>{{ __('messages.Invoice Number') }}</th>     {{-- For FC Department --}}
                                                <th>{{ __('messages.Customer Name') }} </th>
                                                <th>{{ __('messages.Amount') }} </th>
                                                <th>{{ __('messages.Created Time') }}</th>
                                                <th>{{ __('messages.QR Code') }} </th>
                                                <th>{{ __('messages.Action') }}</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                                @php $index = 0; @endphp
                                                @forelse($record as $row)
                                            <tr>
                                                <td>{{ ++$index }}</td>
                                                <td>{{ $row->invoice_number ?? '' }}</td>
                                                <td>{{ $row->customer_name ?? '' }}</td>
                                                <td>{{ $row->amount ?? '' }}</td>
                                                <td> {{ $row->created_at ? strtoupper($row->created_at->format('dMY')) : '' }}</td>
                                                <td> <!-- Display QR Code as SVG -->{!! QrCode::size(100)->generate($row->qr_img_url) !!}</td>
                                                <td>
                                                    <a href="#custom-modal" class="view-record-btn btn btn-primary waves-effect waves-light" data-id="{{ $row->id }}" data-animation="blur" data-plugin="custommodal"
                                                    data-overlaySpeed="100" data-overlayColor="#36404a">{{ __('messages.View') }}</a>
                                                </td>
                                            </tr>
                                            @empty
                                            <tr>
                                                <td></td><td></td><td></td><td></td><td>{{ __('messages.Record not found') }}!</td><td></td><td></td></td><td></td><td></td><td></td><td>
                                            </tr>
                                            @endforelse
                                        
                                            </tbody>
                                        </table>
                                    </div>
                                  
                                </div>
                            </div>
                        </div>
                        <!-- end row -->
<!-- Modal START-->
    <div id="custom-modal" class="modal-demo mm-qr-modal">
        <div class="mm-qrm-head">
            <div class="mm-qrm-title">
                <span class="mm-qrm-title-icon"><i class="mdi mdi-qrcode"></i></span>
                <div>
                    <b>{{ __('messages.Payment QR Code') }}</b>
                    <small>{{ __('messages.Scan to pay this invoice') }}</small>
                </div>
            </div>
            <button type="button" class="mm-qrm-close" onclick="Custombox.modal.close();" aria-label="{{ __('messages.Close') }}">
                <i class="mdi mdi-close"></i>
            </button>
        </div>

        <div class="mm-qrm-body">
            <div class="mm-qrm-qr">
                <div class="mm-qrm-qr-frame">
                    <div id="qr-container"></div>
                    <div class="mm-qrm-loading" id="qr-loading"><span class="mm-spin mm-spin-blue"></span></div>
                </div>
                <span class="mm-qrm-scan"><i class="mdi mdi-cellphone-screenshot"></i> {{ __('messages.Point the camera at the code') }}</span>
            </div>

            <div class="mm-qrm-info">
                <small class="mm-qrm-label">{{ __('messages.Amount to pay') }}</small>
                <div class="mm-qrm-amount"><span class="amount">—</span></div>

                <dl class="mm-qrm-details">
                    <div><dt><i class="mdi mdi-account-outline"></i> {{ __('messages.Customer Name') }}</dt><dd class="customer_name">—</dd></div>
                    <div><dt><i class="mdi mdi-file-document-outline"></i> {{ __('messages.Invoice Number') }}</dt><dd class="invoice_number">—</dd></div>
                    <div><dt><i class="mdi mdi-calendar-clock"></i> {{ __('messages.Created') }}</dt><dd class="created_at">—</dd></div>
                </dl>

                <div class="mm-qrm-link">
                    <input type="text" class="form-control" id="qr-link" readonly aria-label="{{ __('messages.Payment link') }}">
                    <button type="button" class="btn btn-secondary" id="qr-copy"><i class="mdi mdi-content-copy"></i> {{ __('messages.Copy') }}</button>
                </div>

                <div class="mm-qrm-actions">
                    <button type="button" class="btn btn-success" id="qr-download"><i class="mdi mdi-download"></i> {{ __('messages.Download QR') }}</button>
                    <a href="#" class="btn btn-outline-primary" id="qr-open" target="_blank" rel="noopener"><i class="mdi mdi-open-in-new"></i> {{ __('messages.Open Link') }}</a>
                </div>
            </div>
        </div>
    </div>
<!-- Modal END-->
<script>
$(document).ready(function() {
    var current = null;

    $('.view-record-btn').click(function() {
        let recordId = $(this).data('id');
        $('#qr-container').empty();
        $('#qr-loading').show();
        $('.customer_name, .invoice_number, .created_at, .amount').text('—');
        $('#qr-link').val('');

        $.ajax({
            url: '/get-record/' + recordId,
            type: 'GET',
            success: function(response) {
                current = response;
                // .text() so stored values are never rendered as HTML
                $('.customer_name').text(response.customer_name || '—');
                $('.invoice_number').text(response.invoice_number || '—');
                var amount = parseFloat(response.amount);
                $('.amount').text(isNaN(amount) ? (response.amount || '—') : amount.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
                $('.created_at').text(response.created_at ? new Date(response.created_at).toLocaleString(@json(app()->getLocale() === 'th' ? 'th-TH' : 'en-GB'), { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }) : '—');
                $('#qr-link').val(response.qr_img_url || '');
                $('#qr-open').attr('href', response.qr_img_url || '#');

                $('#qr-loading').hide();
                // the popup restores its previous content when reopened, so clear right before drawing
                $('#qr-container').empty();
                new QRCode(document.getElementById('qr-container'), {
                    text: response.qr_img_url,
                    width: 220,
                    height: 220,
                    correctLevel: QRCode.CorrectLevel.M
                });
            },
            error: function() {
                $('#qr-loading').hide();
                toastr.error(@json(__('messages.Failed to fetch record.')));
            }
        });
    });

    $('#qr-copy').on('click', function() {
        var btn = $(this), link = $('#qr-link').val();
        if (!link) return;
        (navigator.clipboard ? navigator.clipboard.writeText(link) : Promise.reject()).catch(function () {
            $('#qr-link').trigger('select'); document.execCommand('copy');
        }).finally(function () {
            btn.html('<i class="mdi mdi-check"></i> ' + @json(__('messages.Copied')));
            setTimeout(function () { btn.html('<i class="mdi mdi-content-copy"></i> ' + @json(__('messages.Copy'))); }, 1500);
        });
    });

    $('#qr-download').on('click', function() {
        var canvas = document.querySelector('#qr-container canvas');
        var img = document.querySelector('#qr-container img');
        var src = canvas ? canvas.toDataURL('image/png') : (img && img.src);
        if (!src) return;
        var a = document.createElement('a');
        a.href = src;
        a.download = 'QR-' + ((current && current.invoice_number) || 'payment').replace(/[^a-z0-9_-]+/gi, '_') + '.png';
        document.body.appendChild(a); a.click(); a.remove();
    });
});
</script>
                   
                        
</div>
