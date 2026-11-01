<section class="distributivo-personal asistencia-personal">
    @php
        $filtros = array_filter(['periodo' => $periodo?->id, 'carga' => $carga?->id]);
        $anual = $trimestre === 'anual';
        $cuantitativa = $tipo === 'cuantitativa';
        $anterior = fn ($matricula, $campo, $valor) => old('calificaciones.'.$matricula.'.'.$campo, $valor);
        $columnasMostradas = max($columnas, (int) collect(old('calificaciones', []))->max(fn ($datos) => count($datos['aportes'] ?? [])));
    @endphp
    <header class="page-header asistencia-encabezado">
        <div class="page-header-copy">
            <h1>Registro de calificaciones</h1>
            <p>Registre las calificaciones trimestrales de sus clases según el Acuerdo MINEDEC-MINEDEC-2026-00082-A.</p>
        </div>
        @if($carga && $tipo !== 'destrezas')
            <a class="asistencia-volver" target="_blank" rel="noopener" href="{{ route('docente.calificaciones.pdf', ['trimestre' => $trimestre] + $filtros) }}">
                <i class="fa-solid fa-file-pdf" aria-hidden="true"></i> Descargar PDF</a>
        @endif
    </header>
    @if(session('success'))
        <p class="sw-alert sw-alert-success" role="status"><i class="fa-solid fa-circle-check" aria-hidden="true"></i> {{ session('success') }}</p>
    @endif
    @if($errors->any())
        <div class="sw-alert sw-alert-error" role="alert"><p>No se guardaron los cambios. Revise lo siguiente:</p>
            <ul>@foreach(collect($errors->all())->unique() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif
    <form method="GET" action="{{ route('docente.calificaciones.index') }}" class="asistencia-filtros">
        <input type="hidden" name="trimestre" value="{{ $trimestre }}">
        <label for="calificacion-periodo">Período académico</label>
        <select name="periodo" id="calificacion-periodo" data-autosubmit>
            @forelse($periodos as $opcion)<option value="{{ $opcion->id }}" @selected($periodo?->id === $opcion->id)>{{ $opcion->nombre }}</option>
            @empty<option value="">Sin períodos</option>@endforelse
        </select>
        <label for="calificacion-carga">Asignatura · curso · paralelo</label>
        <select name="carga" id="calificacion-carga" class="asistencia-carga" data-autosubmit>
            @foreach($cargas as $opcion)<option value="{{ $opcion->id }}" @selected($carga?->id === $opcion->id)>{{ $opcion->asignatura?->nombre ?? $opcion->titulo }} · {{ $opcion->curso->nombre }} {{ $opcion->paralelo->letra }} · {{ $opcion->curso->especialidad?->nombre ?? 'EGB' }}</option>@endforeach
        </select>
        <noscript><button type="submit">Aplicar</button></noscript>
    </form>
    <nav class="asistencia-tabs" aria-label="Trimestres">
        @foreach($trimestres as $numero => $nombre)
            <a href="{{ route('docente.calificaciones.index', ['trimestre' => $numero] + $filtros) }}" @if($trimestre === $numero) class="activo" aria-current="page" @endif>{{ $nombre }}</a>
        @endforeach
        <a href="{{ route('docente.calificaciones.index', ['trimestre' => 'anual'] + $filtros) }}" @if($anual) class="activo" aria-current="page" @endif>Consolidado anual</a>
    </nav>
    @if(! $carga)
        <p class="distributivo-nota">No tiene carga horaria con curso y paralelo asignados en este período.</p>
    @elseif($tipo === 'destrezas')
        <p class="distributivo-nota">Inicial y Preparatoria se registran por ámbito y destreza (art. 35 del Acuerdo), sin notas por aportes. Este registro todavía no está disponible porque el sistema no tiene el catálogo de ámbitos y destrezas.</p>
    @elseif($anual)
        <div class="asistencia-tabla">
            <table>
                <caption>Consolidado anual · {{ $filas->count() }}</caption>
                <thead><tr>
                    <th scope="col" class="asistencia-num">Ord</th><th scope="col">Identificación</th><th scope="col">Apellidos y nombres</th>
                    @foreach($trimestres as $numero => $nombre)<th scope="col" class="asistencia-num"><abbr title="{{ $nombre }}">T{{ $numero }}</abbr></th>@endforeach
                    <th scope="col" class="asistencia-num">Promedio final</th><th scope="col">Situación</th>
                </tr></thead>
                <tbody>
                    @forelse($filas as $fila)
                        <tr>
                            <td class="asistencia-num">{{ $loop->iteration }}</td><td class="asistencia-id">{{ $fila['identificacion'] }}</td><th scope="row">{{ $fila['nombre'] }}</th>
                            @foreach($fila['notas'] as $nota)<td class="asistencia-num">{{ $nota }}</td>@endforeach
                            <td class="asistencia-num"><strong>{{ $fila['promedio_texto'] }}</strong></td><td>{{ $fila['situacion'] }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="8">No hay estudiantes con matrícula activa en este paralelo.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <p class="distributivo-nota">El promedio final es el promedio simple de los tres trimestres, con dos decimales truncados. La situación es referencial para las instituciones que definan sus requisitos de promoción en sus estatutos.</p>
    @else
        <form method="POST" action="{{ route('docente.calificaciones.update') }}">
            @csrf
            @method('PUT')
            <input type="hidden" name="periodo" value="{{ $periodo->id }}">
            <input type="hidden" name="carga" value="{{ $carga->id }}">
            <input type="hidden" name="trimestre" value="{{ $trimestre }}">
            <div class="asistencia-tabla">
                <table data-tabla-calificaciones>
                    <caption>{{ $trimestres[$trimestre] }} · {{ $filas->count() }} estudiantes @if($cuantitativa)· mínimo {{ $minimo }} aportes @endif</caption>
                    <thead><tr>
                        <th scope="col" class="asistencia-num">Ord</th><th scope="col">Apellidos y nombres</th>
                        @if($cuantitativa)
                            @for($i = 1; $i <= $columnasMostradas; $i++)<th scope="col" class="asistencia-num" data-columna-aporte>A{{ $i }}</th>@endfor
                            <th scope="col" class="asistencia-num">Formativa 70 %</th><th scope="col" class="asistencia-num">Sumativa</th>
                            @if($mejoraDisponible)<th scope="col" class="asistencia-num">Mejora</th>@endif
                            <th scope="col" class="asistencia-num">Sumativa 30 %</th><th scope="col" class="asistencia-num">Nota</th>
                        @else
                            <th scope="col">Valoración</th>
                        @endif
                        <th scope="col"><abbr title="Alerta temprana">Alerta</abbr></th>
                    </tr></thead>
                    <tbody>
                        @forelse($filas as $fila)
                            @php($id = $fila['matricula']->id)
                            <tr data-fila="{{ $id }}">
                                <td class="asistencia-num">{{ $loop->iteration }}</td>
                                <th scope="row">{{ $fila['nombre'] }}<br><span class="asistencia-id">{{ $fila['identificacion'] }}</span></th>
                                @if($cuantitativa)
                                    @for($i = 0; $i < $columnasMostradas; $i++)
                                        <td class="calificacion-celda" data-columna-aporte><input class="calificacion-input" type="number" min="0.01" max="10" step="0.01" name="calificaciones[{{ $id }}][aportes][{{ $i }}]" value="{{ $anterior($id, 'aportes.'.$i, $fila['aportes'][$i] ?? null) }}" aria-label="Aporte {{ $i + 1 }} de {{ $fila['nombre'] }}" @error('calificaciones.'.$id.'.aportes.'.$i) aria-invalid="true" @enderror></td>
                                    @endfor
                                    <td class="asistencia-num">{{ \App\Services\Docente\RegistroCalificaciones::formato($fila['formativa']) }}</td>
                                    <td class="calificacion-celda"><input class="calificacion-input" type="number" min="0.01" max="10" step="0.01" name="calificaciones[{{ $id }}][sumativa]" value="{{ $anterior($id, 'sumativa', $fila['registro']?->sumativa) }}" aria-label="Sumativa de {{ $fila['nombre'] }}" @error('calificaciones.'.$id.'.sumativa') aria-invalid="true" @enderror></td>
                                    @if($mejoraDisponible)
                                        <td class="calificacion-celda"><input class="calificacion-input" type="number" min="0.01" max="10" step="0.01" name="calificaciones[{{ $id }}][mejora]" value="{{ $anterior($id, 'mejora', $fila['registro']?->sumativa_mejora) }}" aria-label="Evaluación de mejora de {{ $fila['nombre'] }}" @error('calificaciones.'.$id.'.mejora') aria-invalid="true" @enderror></td>
                                    @endif
                                    <td class="asistencia-num">{{ \App\Services\Docente\RegistroCalificaciones::formato($fila['sumativa_final']) }}</td>
                                    <td class="asistencia-num"><strong>{{ $fila['nota_texto'] }}</strong></td>
                                @else
                                    <td><select class="calificacion-input calificacion-input--letra" name="calificaciones[{{ $id }}][valoracion]" aria-label="Valoración de {{ $fila['nombre'] }}" @error('calificaciones.'.$id.'.valoracion') aria-invalid="true" @enderror>
                                        <option value="">—</option>
                                        @foreach($escala as $letra)<option value="{{ $letra }}" @selected($anterior($id, 'valoracion', $fila['registro']?->valoracion) === $letra)>{{ $letra }}</option>@endforeach
                                    </select></td>
                                @endif
                                <td>@if($fila['alerta'])<span class="asistencia-conteo asistencia-estado--f">Sí</span>@else — @endif</td>
                            </tr>
                        @empty
                            <tr><td colspan="{{ $cuantitativa ? 7 + $columnasMostradas : 4 }}">No hay estudiantes con matrícula activa en este paralelo.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($filas->isNotEmpty())
                <div class="asistencia-acciones calificacion-acciones">
                    <button class="asistencia-boton" type="submit">Guardar calificaciones</button>
                    @if($cuantitativa)<button class="asistencia-volver" type="button" data-agregar-aporte>Agregar aporte</button>@endif
                </div>
            @endif
        </form>
        @if($cuantitativa)
            <p class="distributivo-nota">Nota = formativa × 70 % + sumativa × 30 %, con dos decimales truncados. La formativa es el promedio de los aportes y se calcula desde {{ $minimo }} aportes registrados, según los períodos semanales de la clase. La alerta temprana se activa con formativa de 0,01 a 6,99. @if($mejoraDisponible)La mejora promedia la sumativa inicial con la evaluación adicional: hasta 9,00 si la inicial es superior a 7,00 e inferior a 9,00, y hasta 7,00 si es inferior a 7,00.@else Este período se rige por el Acuerdo 2024-00031-A en lo referente a la mejora: registre en Sumativa la nota resultante.@endif</p>
        @else
            <p class="distributivo-nota">Escala cualitativa: A+ a B- destreza o aprendizaje alcanzado; C+ y C- en proceso de desarrollo; D+ a E- iniciado. La alerta temprana se activa de E- a C+.</p>
        @endif
    @endif
</section>
<script>
    document.querySelectorAll('.asistencia-personal [data-autosubmit]').forEach((select) => {
        select.addEventListener('change', () => select.form.requestSubmit());
    });
    document.querySelector('[data-agregar-aporte]')?.addEventListener('click', () => {
        const tabla = document.querySelector('[data-tabla-calificaciones]');
        const indice = tabla.querySelectorAll('thead [data-columna-aporte]').length;
        const encabezado = tabla.querySelector('thead [data-columna-aporte]:last-of-type').cloneNode(false);
        encabezado.textContent = `A${indice + 1}`;
        tabla.querySelectorAll('thead [data-columna-aporte]')[indice - 1].after(encabezado);
        tabla.querySelectorAll('tbody tr[data-fila]').forEach((fila) => {
            const celdas = fila.querySelectorAll('[data-columna-aporte]');
            const celda = celdas[indice - 1].cloneNode(true);
            const campo = celda.querySelector('input');
            campo.name = `calificaciones[${fila.dataset.fila}][aportes][${indice}]`;
            campo.value = '';
            campo.setAttribute('aria-label', campo.getAttribute('aria-label').replace(/^Aporte \d+/, `Aporte ${indice + 1}`));
            campo.removeAttribute('aria-invalid');
            celdas[indice - 1].after(celda);
        });
    });
</script>
