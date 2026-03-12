{{-- Detailansicht einer Rechnung --}}
{{-- Zugriff: Nur Administratoren --}}
<x-app-layout>

    {{-- Erfolgsmeldung nach Erstellung – PDF wird automatisch heruntergeladen --}}
    @if(session('auto_download') && $rechnung->pdf_pfad)
        <div class="alert alert-success mb-4 shadow-sm" role="alert">
            <strong>Rechnung {{ $rechnung->rechnungsnummer }} wurde erfolgreich erstellt.</strong>
            Das PDF wird automatisch heruntergeladen.
        </div>
        {{-- window.location.href navigiert zum Download-Link ohne Seitenwechsel --}}
        {{-- Chrome erlaubt diese Art der Navigation (kein Popup-Blocker) --}}
        <script>
            window.location.href = '{{ route('admin.rechnungen.download', $rechnung) }}';
        </script>
    @endif

    {{-- Seitenkopf --}}
    <div class="row mb-4 align-items-center">
        <div class="col">
            <h4 class="fw-bold mb-0">{{ $rechnung->rechnungsnummer }}</h4>
            <p class="text-muted small mb-0">
                <a href="{{ route('admin.rechnungen.index') }}" class="text-decoration-none">Rechnungen</a>
                &rsaquo; {{ $rechnung->rechnungsnummer }}
            </p>
        </div>
        <div class="col-auto d-flex gap-2">
            {{-- PDF herunterladen --}}
            @if($rechnung->pdf_pfad)
                <a id="btn-pdf-download"
                   href="{{ route('admin.rechnungen.download', $rechnung) }}"
                   class="btn btn-outline-secondary btn-sm">
                    PDF herunterladen
                </a>
            @endif
            {{-- Als bezahlt markieren --}}
            @if($rechnung->status === 'offen')
                <form method="POST" action="{{ route('admin.rechnungen.bezahlt', $rechnung) }}">
                    @csrf
                    <button type="submit" class="btn btn-success btn-sm">
                        Als bezahlt markieren
                    </button>
                </form>
            @endif
        </div>
    </div>

    <div class="row g-4">

        {{-- Rechnungsdetails --}}
        <div class="col-md-5">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-semibold">Rechnungsdetails</div>
                <div class="card-body">
                    <table class="table table-borderless mb-0">
                        <tr>
                            <td class="text-muted" style="width:45%">Rechnungsnummer</td>
                            <td class="fw-semibold">{{ $rechnung->rechnungsnummer }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Auftraggeber</td>
                            <td>{{ $rechnung->auftraggeber->firmenname }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Rechnungsdatum</td>
                            <td>{{ $rechnung->rechnungsdatum?->format('d.m.Y') ?? '–' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Zeitraum</td>
                            <td>
                                {{ $rechnung->zeitraum_von->format('d.m.Y') }}
                                – {{ $rechnung->zeitraum_bis->format('d.m.Y') }}
                            </td>
                        </tr>
                        <tr><td colspan="2"><hr class="my-1"></td></tr>
                        <tr>
                            <td class="text-muted">Nettobetrag</td>
                            <td>{{ number_format($rechnung->nettobetrag, 2, ',', '.') }} €</td>
                        </tr>
                        <tr>
                            <td class="text-muted">MwSt (19%)</td>
                            <td>{{ number_format($rechnung->mwst_betrag, 2, ',', '.') }} €</td>
                        </tr>
                        <tr>
                            <td class="fw-bold">Gesamtbetrag</td>
                            <td class="fw-bold text-primary fs-5">
                                {{ number_format($rechnung->gesamtbetrag, 2, ',', '.') }} €
                            </td>
                        </tr>
                        <tr><td colspan="2"><hr class="my-1"></td></tr>
                        <tr>
                            <td class="text-muted">Status</td>
                            <td>
                                @if($rechnung->status === 'bezahlt')
                                    <span class="badge badge-status bg-success fs-6">Bezahlt</span>
                                @elseif($rechnung->status === 'storniert')
                                    <span class="badge badge-status bg-danger fs-6">Storniert</span>
                                @else
                                    <span class="badge badge-status badge-orange fs-6">Offen</span>
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        {{-- Kontaktdaten des Auftraggebers --}}
        <div class="col-md-7">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-semibold">Auftraggeber</div>
                <div class="card-body">
                    <table class="table table-borderless mb-0">
                        <tr>
                            <td class="text-muted" style="width:40%">Firma</td>
                            <td>{{ $rechnung->auftraggeber->firmenname }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Ansprechpartner</td>
                            <td>{{ $rechnung->auftraggeber->ansprechpartner }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">E-Mail</td>
                            <td>{{ $rechnung->auftraggeber->email }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Adresse</td>
                            <td>{{ $rechnung->auftraggeber->adresse }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

    </div>

</x-app-layout>
