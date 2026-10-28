<section class="distributivo-personal">
    <header class="page-header">
        <div class="page-header-copy">
            <h1>Horario Personal</h1>
            <p>Su horario semanal de clases, organizado por días y horas.</p>
        </div>
    </header>

    @if ($franjas->isNotEmpty())
        <div class="distributivo-tabla horario-personal-tabla">
            <table class="sw-table">
                <caption>Horario semanal</caption>
                <thead>
                    <tr>
                        <th scope="col">Hora</th>
                        @foreach ($dias as $dia)
                            <th scope="col">{{ $dia }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach ($franjas as $franja)
                        <tr>
                            <th scope="row">{{ $franja['inicio'] }} – {{ $franja['fin'] }}</th>
                            @foreach ($dias as $numero => $dia)
                                <td>
                                    @forelse ($franja['dias']->get($numero, collect()) as $bloque)
                                        <div class="horario-personal-bloque">
                                            <strong>{{ $bloque->asignatura }}</strong>
                                            <span>{{ $bloque->curso }}</span>
                                            <span>{{ $bloque->aula ? 'Aula: '.$bloque->aula : 'Sin aula asignada' }}</span>
                                        </div>
                                    @empty
                                        <span aria-label="Sin clase">—</span>
                                    @endforelse
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <p class="distributivo-nota">Sin horario asignado. Aún no tiene bloques horarios registrados.</p>
    @endif
</section>
