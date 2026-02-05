import React, { useMemo, useState } from 'react';
import DashboardLayout from '../../components/DashboardLayout';
import './AulaVirtual.css';

const mockAsignaciones = [
  { id: 1, curso: 'Matemática', grado: '1° Sec', seccion: 'A', docente: 'Doc. Pérez' },
  { id: 2, curso: 'Comunicación', grado: '1° Sec', seccion: 'A', docente: 'Doc. Rojas' },
  { id: 3, curso: 'Ciencia y Tecnología', grado: '2° Sec', seccion: 'B', docente: 'Doc. García' },
  { id: 4, curso: 'Historia', grado: '3° Sec', seccion: 'C', docente: 'Doc. Flores' },
];

export default function AulaVirtual() {
  const [search, setSearch] = useState('');
  const [filter, setFilter] = useState('Todos');
  const [activeId, setActiveId] = useState(1);
  const [mundoActivo, setMundoActivo] = useState(1);

  const grados = useMemo(() => ['Todos', ...Array.from(new Set(mockAsignaciones.map((a) => a.grado)))], []);

  const asignacionesFiltradas = useMemo(() => {
    const q = search.trim().toLowerCase();
    return mockAsignaciones.filter((a) => {
      const matchSearch = !q || `${a.curso} ${a.grado} ${a.seccion} ${a.docente}`.toLowerCase().includes(q);
      const matchFilter = filter === 'Todos' || a.grado === filter;
      return matchSearch && matchFilter;
    });
  }, [search, filter]);

  const active = useMemo(() => asignacionesFiltradas.find((a) => a.id === activeId) || asignacionesFiltradas[0], [asignacionesFiltradas, activeId]);

  return (
    <DashboardLayout>
      <div className="aula-container">
        <div className="aula-sidebar">
          <div className="aula-sidebar-header">
            <h2>Asignaciones</h2>
            <input
              type="text"
              className="aula-search"
              placeholder="Buscar..."
              value={search}
              onChange={(e) => setSearch(e.target.value)}
            />
            <select className="aula-filter" value={filter} onChange={(e) => setFilter(e.target.value)}>
              {grados.map((g) => (
                <option key={g} value={g}>
                  {g === 'Todos' ? 'Todos los grados' : g}
                </option>
              ))}
            </select>
          </div>

          <div className="aula-sidebar-lista">
            {asignacionesFiltradas.length === 0 ? (
              <div className="empty-state">
                <p>No se encontraron asignaciones.</p>
              </div>
            ) : (
              asignacionesFiltradas.map((a) => (
                <button
                  key={a.id}
                  type="button"
                  className={`aula-asignacion ${a.id === activeId ? 'active' : ''}`}
                  onClick={() => setActiveId(a.id)}
                >
                  <div className="aula-asignacion-curso">{a.curso}</div>
                  <div className="aula-asignacion-detalle">
                    <span>{a.grado}</span>
                    <span>·</span>
                    <span>{a.seccion}</span>
                    <span>·</span>
                    <span>{a.docente}</span>
                  </div>
                </button>
              ))
            )}
          </div>
        </div>

        <div className="aula-content">
          <div className="aula-panel">
            <div className="aula-panel-header">
              <div>
                <h1>{active ? active.curso : 'Aula Virtual'}</h1>
                <p>
                  {active ? `${active.grado} · Sección ${active.seccion} · ${active.docente}` : 'Selecciona una asignación para ver el contenido.'}
                </p>
              </div>

              <div className="aula-panel-actions">
                <button type="button" className="btn-outline">
                  Ver notas
                </button>
                <button type="button" className="btn-outline">
                  Ayuda
                </button>
              </div>
            </div>

            <div className="aula-mundos">
              <h2>Mundos (Bimestres)</h2>
              <div className="aula-mundos-grid">
                {[1, 2, 3, 4].map((n) => (
                  <div
                    key={n}
                    className={`mundo-card ${mundoActivo === n ? 'active' : ''}`}
                    role="button"
                    tabIndex={0}
                    onClick={() => setMundoActivo(n)}
                    onKeyDown={(e) => {
                      if (e.key === 'Enter' || e.key === ' ') setMundoActivo(n);
                    }}
                  >
                    <div className="mundo-numero">Mundo {n}</div>
                    <div className="mundo-objetivo">Objetivo: completar temas y actividades del bimestre</div>
                    <div className="mundo-indicadores">
                      <div>
                        <strong>—</strong>
                        <div>
                          <span>Temas</span>
                        </div>
                      </div>
                      <div>
                        <strong>—</strong>
                        <div>
                          <span>Tareas</span>
                        </div>
                      </div>
                      <div>
                        <strong>—</strong>
                        <div>
                          <span>Exámenes</span>
                        </div>
                      </div>
                    </div>
                  </div>
                ))}
              </div>
            </div>

            <div className="aula-seccion-grid">
              <div className="aula-seccion">
                <div className="aula-seccion-header">
                  <h3>Temas</h3>
                  <span className="badge-soft">—</span>
                </div>
                <div className="aula-lista">
                  <div className="aula-item">
                    <div className="aula-item-info">
                      <strong>Tema 1</strong>
                      <p>Contenido interactivo y recursos</p>
                    </div>
                    <div className="aula-item-actions">
                      <button className="aula-item-link" type="button" title="Abrir">
                        ➜
                      </button>
                    </div>
                  </div>
                </div>
              </div>

              <div className="aula-seccion">
                <div className="aula-seccion-header">
                  <h3>Tareas</h3>
                  <span className="badge-soft">—</span>
                </div>
                <div className="aula-lista">
                  <div className="aula-item">
                    <div className="aula-item-info">
                      <strong>Tarea 1</strong>
                      <p>Entrega online</p>
                    </div>
                    <div className="aula-item-actions">
                      <span className="badge-preguntas">Pendiente</span>
                      <button className="aula-item-link" type="button" title="Abrir">
                        ➜
                      </button>
                    </div>
                  </div>
                </div>
              </div>

              <div className="aula-seccion">
                <div className="aula-seccion-header">
                  <h3>Exámenes</h3>
                  <span className="badge-soft">—</span>
                </div>
                <div className="aula-lista">
                  <div className="aula-item">
                    <div className="aula-item-info">
                      <strong>Examen 1</strong>
                      <p>Evaluación online</p>
                    </div>
                    <div className="aula-item-actions">
                      <span className="badge-preguntas">— preguntas</span>
                      <button className="aula-item-link" type="button" title="Abrir">
                        ➜
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </DashboardLayout>
  );
}


