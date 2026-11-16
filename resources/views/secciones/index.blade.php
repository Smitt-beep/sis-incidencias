@extends('layouts.app')

@section('title', 'Gestión de Grados')

@section('content')
    <style>
        .grade-courses-menu {
            min-width: 13rem;
            width: min(18rem, calc(100vw - 3rem));
            z-index: 1090;
        }

        .grade-course-item {
            position: relative;
        }

        .grade-course-toggle {
            align-items: center;
            display: flex;
            justify-content: space-between;
            white-space: normal;
            width: 100%;
        }

        .grade-course-submenu {
            display: none;
            left: calc(100% - 1px);
            min-width: 14rem;
            position: absolute;
            top: -.25rem;
            z-index: 1091;
        }

        .grade-course-item:hover > .grade-course-submenu,
        .grade-course-item:focus-within > .grade-course-submenu,
        .grade-course-item.is-open > .grade-course-submenu {
            display: block;
        }

        .grade-course-empty {
            color: #6c757d;
            font-size: .875rem;
            padding: .5rem .75rem;
        }

        @media (max-width: 767.98px) {
            .grade-course-submenu {
                border: 0;
                box-shadow: none;
                left: auto;
                margin: 0;
                min-width: 0;
                padding-left: 1rem;
                position: static;
                width: 100%;
            }

            .grade-course-item:hover > .grade-course-submenu,
            .grade-course-item:focus-within > .grade-course-submenu {
                display: none;
            }

            .grade-course-item.is-open > .grade-course-submenu {
                display: block;
            }
        }
    </style>

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h1 class="h2 mb-1">Gestión de Grados</h1>
            <p class="text-secondary mb-0">Consulta los cursos y secciones registrados por grado.</p>
        </div>
        <a href="{{ route('secciones.create') }}" class="btn btn-primary">Nueva sección</a>
    </div>

    <div class="card border-0 shadow-sm">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th scope="col">Grado</th>
                    <th scope="col">Cursos</th>
                    <th scope="col" class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($grados as $grado)
                    <tr data-grade-row="{{ $loop->index }}">
                        <th scope="row" class="fw-semibold">{{ $grado['nombre'] }}</th>
                        <td>
                            <div class="dropdown grade-courses-dropdown">
                                <button
                                    class="btn btn-sm btn-outline-primary dropdown-toggle"
                                    type="button"
                                    id="grade-courses-{{ $loop->index }}"
                                    data-bs-toggle="dropdown"
                                    data-bs-auto-close="outside"
                                    aria-expanded="false"
                                    aria-controls="grade-courses-menu-{{ $loop->index }}"
                                >
                                    Ver cursos
                                    <span class="badge text-bg-primary ms-1">{{ $grado['cursos']->count() }}</span>
                                </button>
                                <div
                                    class="dropdown-menu grade-courses-menu p-2"
                                    id="grade-courses-menu-{{ $loop->index }}"
                                    aria-labelledby="grade-courses-{{ $loop->index }}"
                                >
                                    @forelse ($grado['cursos'] as $cursoIndex => $curso)
                                        <div class="grade-course-item">
                                            <button
                                                class="dropdown-item grade-course-toggle"
                                                type="button"
                                                aria-expanded="false"
                                                aria-controls="grade-course-sections-{{ $loop->parent->index }}-{{ $cursoIndex }}"
                                            >
                                                <span>{{ $curso['nombre'] }}</span>
                                                <span class="ms-2" aria-hidden="true">&rsaquo;</span>
                                            </button>
                                            <div
                                                class="dropdown-menu grade-course-submenu"
                                                id="grade-course-sections-{{ $loop->parent->index }}-{{ $cursoIndex }}"
                                            >
                                                @forelse ($curso['secciones'] as $seccion)
                                                    <a
                                                        class="dropdown-item"
                                                        href="{{ route('secciones.edit', $seccion['id']) }}"
                                                    >
                                                        Sección {{ $seccion['nombre'] }}
                                                    </a>
                                                @empty
                                                    <div class="grade-course-empty">Sin secciones registradas</div>
                                                @endforelse
                                            </div>
                                        </div>
                                    @empty
                                        <div class="grade-course-empty">Sin cursos registrados</div>
                                    @endforelse
                                </div>
                            </div>
                        </td>
                        <td class="text-end">
                            <div class="dropdown">
                                <button
                                    class="btn btn-sm btn-outline-secondary dropdown-toggle"
                                    type="button"
                                    id="grade-actions-{{ $loop->index }}"
                                    data-bs-toggle="dropdown"
                                    aria-expanded="false"
                                >
                                    Editar secciones
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="grade-actions-{{ $loop->index }}">
                                    @foreach ($grado['secciones'] as $seccion)
                                        <li>
                                            <a class="dropdown-item" href="{{ route('secciones.edit', $seccion) }}">
                                                Editar {{ $seccion->seccion }}
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="text-center text-secondary py-4">No hay grados registrados.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <script>
        document.querySelectorAll('.grade-course-toggle').forEach((button) => {
            button.addEventListener('click', () => {
                const item = button.closest('.grade-course-item');
                const shouldOpen = !item.classList.contains('is-open');

                item.parentElement.querySelectorAll('.grade-course-item.is-open').forEach((openItem) => {
                    openItem.classList.remove('is-open');
                    openItem.querySelector('.grade-course-toggle').setAttribute('aria-expanded', 'false');
                });

                item.classList.toggle('is-open', shouldOpen);
                button.setAttribute('aria-expanded', shouldOpen ? 'true' : 'false');
            });
        });

        document.querySelectorAll('.grade-courses-dropdown').forEach((dropdown) => {
            dropdown.addEventListener('hide.bs.dropdown', () => {
                dropdown.querySelectorAll('.grade-course-item.is-open').forEach((item) => {
                    item.classList.remove('is-open');
                    item.querySelector('.grade-course-toggle').setAttribute('aria-expanded', 'false');
                });
            });
        });
    </script>
@endsection
