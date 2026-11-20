--
-- PostgreSQL database dump
--

\restrict PCJvGCHy4S9K2eTjCGoHkFqugHkxzw9equdrcgALbJJbWIVjmUsaZc8NtJ3M6yu

-- Dumped from database version 18.6
-- Dumped by pg_dump version 18.6

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET transaction_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- Name: asignaciones_docentes; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.asignaciones_docentes (
    id bigint NOT NULL,
    usuario_id bigint NOT NULL,
    curso_id bigint NOT NULL,
    seccion_id bigint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.asignaciones_docentes OWNER TO postgres;

--
-- Name: asignaciones_docentes_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.asignaciones_docentes_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.asignaciones_docentes_id_seq OWNER TO postgres;

--
-- Name: asignaciones_docentes_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.asignaciones_docentes_id_seq OWNED BY public.asignaciones_docentes.id;


--
-- Name: cache; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.cache (
    key character varying(255) NOT NULL,
    value text NOT NULL,
    expiration integer NOT NULL
);


ALTER TABLE public.cache OWNER TO postgres;

--
-- Name: cache_locks; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.cache_locks (
    key character varying(255) NOT NULL,
    owner character varying(255) NOT NULL,
    expiration integer NOT NULL
);


ALTER TABLE public.cache_locks OWNER TO postgres;

--
-- Name: chatbot_conversaciones; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.chatbot_conversaciones (
    id bigint NOT NULL,
    usuario_id bigint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.chatbot_conversaciones OWNER TO postgres;

--
-- Name: chatbot_conversaciones_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.chatbot_conversaciones_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.chatbot_conversaciones_id_seq OWNER TO postgres;

--
-- Name: chatbot_conversaciones_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.chatbot_conversaciones_id_seq OWNED BY public.chatbot_conversaciones.id;


--
-- Name: chatbot_mensajes; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.chatbot_mensajes (
    id bigint NOT NULL,
    conversacion_id bigint NOT NULL,
    emisor character varying(255) NOT NULL,
    contenido text NOT NULL,
    estado character varying(255) NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    CONSTRAINT chatbot_mensajes_emisor_check CHECK (((emisor)::text = ANY ((ARRAY['usuario'::character varying, 'asistente'::character varying])::text[]))),
    CONSTRAINT chatbot_mensajes_estado_check CHECK (((estado)::text = ANY ((ARRAY['completado'::character varying, 'bloqueado'::character varying, 'error'::character varying])::text[])))
);


ALTER TABLE public.chatbot_mensajes OWNER TO postgres;

--
-- Name: chatbot_mensajes_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.chatbot_mensajes_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.chatbot_mensajes_id_seq OWNER TO postgres;

--
-- Name: chatbot_mensajes_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.chatbot_mensajes_id_seq OWNED BY public.chatbot_mensajes.id;


--
-- Name: cursos; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.cursos (
    id bigint NOT NULL,
    nombre character varying(255) NOT NULL
);


ALTER TABLE public.cursos OWNER TO postgres;

--
-- Name: cursos_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.cursos_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.cursos_id_seq OWNER TO postgres;

--
-- Name: cursos_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.cursos_id_seq OWNED BY public.cursos.id;


--
-- Name: estados; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.estados (
    id bigint NOT NULL,
    nombre character varying(50) NOT NULL
);


ALTER TABLE public.estados OWNER TO postgres;

--
-- Name: estados_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.estados_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.estados_id_seq OWNER TO postgres;

--
-- Name: estados_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.estados_id_seq OWNED BY public.estados.id;


--
-- Name: estudiantes; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.estudiantes (
    id bigint NOT NULL,
    dni character varying(255) NOT NULL,
    nombre character varying(255) NOT NULL,
    seccion_id bigint NOT NULL
);


ALTER TABLE public.estudiantes OWNER TO postgres;

--
-- Name: estudiantes_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.estudiantes_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.estudiantes_id_seq OWNER TO postgres;

--
-- Name: estudiantes_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.estudiantes_id_seq OWNED BY public.estudiantes.id;


--
-- Name: failed_jobs; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.failed_jobs (
    id bigint NOT NULL,
    uuid character varying(255) NOT NULL,
    connection text NOT NULL,
    queue text NOT NULL,
    payload text NOT NULL,
    exception text NOT NULL,
    failed_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
);


ALTER TABLE public.failed_jobs OWNER TO postgres;

--
-- Name: failed_jobs_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.failed_jobs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.failed_jobs_id_seq OWNER TO postgres;

--
-- Name: failed_jobs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.failed_jobs_id_seq OWNED BY public.failed_jobs.id;


--
-- Name: gravedades; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.gravedades (
    id bigint NOT NULL,
    nombre character varying(50) NOT NULL
);


ALTER TABLE public.gravedades OWNER TO postgres;

--
-- Name: gravedades_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.gravedades_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.gravedades_id_seq OWNER TO postgres;

--
-- Name: gravedades_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.gravedades_id_seq OWNED BY public.gravedades.id;


--
-- Name: incidencia_fotografias; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.incidencia_fotografias (
    id bigint NOT NULL,
    incidencia_id bigint NOT NULL,
    ruta_archivo character varying(255) NOT NULL,
    nombre_original character varying(255) NOT NULL,
    tipo_mime character varying(100) NOT NULL,
    tamano bigint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone,
    eliminado_por bigint
);


ALTER TABLE public.incidencia_fotografias OWNER TO postgres;

--
-- Name: incidencia_fotografias_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.incidencia_fotografias_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.incidencia_fotografias_id_seq OWNER TO postgres;

--
-- Name: incidencia_fotografias_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.incidencia_fotografias_id_seq OWNED BY public.incidencia_fotografias.id;


--
-- Name: incidencia_tipo; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.incidencia_tipo (
    incidencia_id bigint NOT NULL,
    tipo_incidencia_id bigint NOT NULL
);


ALTER TABLE public.incidencia_tipo OWNER TO postgres;

--
-- Name: incidencias; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.incidencias (
    id bigint NOT NULL,
    estudiante_id bigint NOT NULL,
    usuario_id bigint NOT NULL,
    curso_id bigint NOT NULL,
    fecha date NOT NULL,
    hora time(0) without time zone NOT NULL,
    descripcion text NOT NULL,
    gravedad_id bigint NOT NULL,
    estado_id bigint NOT NULL,
    tipo_aula character varying(10),
    deleted_at timestamp(0) without time zone,
    eliminado_por bigint,
    CONSTRAINT incidencias_tipo_aula_valid_check CHECK (((tipo_aula IS NULL) OR ((tipo_aula)::text = ANY ((ARRAY['teoria'::character varying, 'taller'::character varying])::text[]))))
);


ALTER TABLE public.incidencias OWNER TO postgres;

--
-- Name: incidencias_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.incidencias_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.incidencias_id_seq OWNER TO postgres;

--
-- Name: incidencias_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.incidencias_id_seq OWNED BY public.incidencias.id;


--
-- Name: job_batches; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.job_batches (
    id character varying(255) NOT NULL,
    name character varying(255) NOT NULL,
    total_jobs integer NOT NULL,
    pending_jobs integer NOT NULL,
    failed_jobs integer NOT NULL,
    failed_job_ids text NOT NULL,
    options text,
    cancelled_at integer,
    created_at integer NOT NULL,
    finished_at integer
);


ALTER TABLE public.job_batches OWNER TO postgres;

--
-- Name: jobs; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.jobs (
    id bigint NOT NULL,
    queue character varying(255) NOT NULL,
    payload text NOT NULL,
    attempts smallint NOT NULL,
    reserved_at integer,
    available_at integer NOT NULL,
    created_at integer NOT NULL
);


ALTER TABLE public.jobs OWNER TO postgres;

--
-- Name: jobs_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.jobs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.jobs_id_seq OWNER TO postgres;

--
-- Name: jobs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.jobs_id_seq OWNED BY public.jobs.id;


--
-- Name: migrations; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.migrations (
    id integer NOT NULL,
    migration character varying(255) NOT NULL,
    batch integer NOT NULL
);


ALTER TABLE public.migrations OWNER TO postgres;

--
-- Name: migrations_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.migrations_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.migrations_id_seq OWNER TO postgres;

--
-- Name: migrations_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.migrations_id_seq OWNED BY public.migrations.id;


--
-- Name: password_reset_tokens; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.password_reset_tokens (
    email character varying(255) NOT NULL,
    token character varying(255) NOT NULL,
    created_at timestamp(0) without time zone
);


ALTER TABLE public.password_reset_tokens OWNER TO postgres;

--
-- Name: roles; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.roles (
    id bigint NOT NULL,
    nombre_rol character varying(255) NOT NULL
);


ALTER TABLE public.roles OWNER TO postgres;

--
-- Name: roles_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.roles_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.roles_id_seq OWNER TO postgres;

--
-- Name: roles_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.roles_id_seq OWNED BY public.roles.id;


--
-- Name: secciones; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.secciones (
    id bigint NOT NULL,
    grado character varying(255) NOT NULL,
    seccion character varying(255) NOT NULL
);


ALTER TABLE public.secciones OWNER TO postgres;

--
-- Name: secciones_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.secciones_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.secciones_id_seq OWNER TO postgres;

--
-- Name: secciones_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.secciones_id_seq OWNED BY public.secciones.id;


--
-- Name: sessions; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.sessions (
    id character varying(255) NOT NULL,
    user_id bigint,
    ip_address character varying(45),
    user_agent text,
    payload text NOT NULL,
    last_activity integer NOT NULL
);


ALTER TABLE public.sessions OWNER TO postgres;

--
-- Name: tipos_incidencia; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.tipos_incidencia (
    id bigint NOT NULL,
    nombre character varying(255) NOT NULL
);


ALTER TABLE public.tipos_incidencia OWNER TO postgres;

--
-- Name: tipos_incidencia_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.tipos_incidencia_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.tipos_incidencia_id_seq OWNER TO postgres;

--
-- Name: tipos_incidencia_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.tipos_incidencia_id_seq OWNED BY public.tipos_incidencia.id;


--
-- Name: users; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.users (
    id bigint NOT NULL,
    name character varying(255) NOT NULL,
    email character varying(255) NOT NULL,
    dni character varying(8) NOT NULL,
    email_verified_at timestamp(0) without time zone,
    password character varying(255) NOT NULL,
    remember_token character varying(100),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    rol_id bigint,
    estado_aprobacion character varying(255) DEFAULT 'Pendiente'::character varying NOT NULL,
    activo boolean DEFAULT true NOT NULL,
    grado_registro character varying(255),
    curso_registro_id bigint
);


ALTER TABLE public.users OWNER TO postgres;

--
-- Name: users_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.users_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.users_id_seq OWNER TO postgres;

--
-- Name: users_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.users_id_seq OWNED BY public.users.id;


--
-- Name: asignaciones_docentes id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.asignaciones_docentes ALTER COLUMN id SET DEFAULT nextval('public.asignaciones_docentes_id_seq'::regclass);


--
-- Name: chatbot_conversaciones id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.chatbot_conversaciones ALTER COLUMN id SET DEFAULT nextval('public.chatbot_conversaciones_id_seq'::regclass);


--
-- Name: chatbot_mensajes id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.chatbot_mensajes ALTER COLUMN id SET DEFAULT nextval('public.chatbot_mensajes_id_seq'::regclass);


--
-- Name: cursos id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.cursos ALTER COLUMN id SET DEFAULT nextval('public.cursos_id_seq'::regclass);


--
-- Name: estados id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.estados ALTER COLUMN id SET DEFAULT nextval('public.estados_id_seq'::regclass);


--
-- Name: estudiantes id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.estudiantes ALTER COLUMN id SET DEFAULT nextval('public.estudiantes_id_seq'::regclass);


--
-- Name: failed_jobs id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.failed_jobs ALTER COLUMN id SET DEFAULT nextval('public.failed_jobs_id_seq'::regclass);


--
-- Name: gravedades id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.gravedades ALTER COLUMN id SET DEFAULT nextval('public.gravedades_id_seq'::regclass);


--
-- Name: incidencia_fotografias id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.incidencia_fotografias ALTER COLUMN id SET DEFAULT nextval('public.incidencia_fotografias_id_seq'::regclass);


--
-- Name: incidencias id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.incidencias ALTER COLUMN id SET DEFAULT nextval('public.incidencias_id_seq'::regclass);


--
-- Name: jobs id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.jobs ALTER COLUMN id SET DEFAULT nextval('public.jobs_id_seq'::regclass);


--
-- Name: migrations id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.migrations ALTER COLUMN id SET DEFAULT nextval('public.migrations_id_seq'::regclass);


--
-- Name: roles id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.roles ALTER COLUMN id SET DEFAULT nextval('public.roles_id_seq'::regclass);


--
-- Name: secciones id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.secciones ALTER COLUMN id SET DEFAULT nextval('public.secciones_id_seq'::regclass);


--
-- Name: tipos_incidencia id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.tipos_incidencia ALTER COLUMN id SET DEFAULT nextval('public.tipos_incidencia_id_seq'::regclass);


--
-- Name: users id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users ALTER COLUMN id SET DEFAULT nextval('public.users_id_seq'::regclass);


--
-- Data for Name: asignaciones_docentes; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.asignaciones_docentes (id, usuario_id, curso_id, seccion_id, created_at, updated_at) FROM stdin;
1	2	1	1	2026-11-10 15:59:05	2026-11-10 15:59:05
2	2	1	2	2026-11-10 15:59:05	2026-11-10 15:59:05
3	8	2	1	2026-11-11 16:51:38	2026-11-11 16:51:38
4	8	2	2	2026-11-11 16:51:38	2026-11-11 16:51:38
\.


--
-- Data for Name: cache; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.cache (key, value, expiration) FROM stdin;
laravel-cache-chatbot_q_cba7fc226c92951121b9b3c33d6cf11c	s:1810:"Hola, soy TungaBot, tu asistente para el sistema de incidencias de la Institución Educativa. Para reportar una incidencia (por ejemplo, un conflicto entre estudiantes, un acto de indisciplina o una situación que afecte la convivencia), sigue estos pasos:\n\n1. **Accede al sistema**: Ingresa a la plataforma de incidencias de la institución (puede ser un portal web, una app o un formulario físico, según tu rol y los recursos disponibles).\n\n2. **Inicia sesión**: Usa tu usuario y contraseña. Si eres estudiante, docente, administrativo o padre de familia, asegúrate de tener los permisos correspondientes.\n\n3. **Selecciona “Reportar incidencia”**: Busca la opción para crear un nuevo reporte.\n\n4. **Completa los datos obligatorios**:\n   - **Fecha y hora** del incidente.\n   - **Lugar** (aula, patio, pasillo, etc.).\n   - **Personas involucradas** (nombres completos, curso/grado si aplica).\n   - **Descripción detallada**: Explica qué ocurrió, de manera objetiva y sin juicios de valor. Incluye testigos si los hay.\n   - **Tipo de incidencia**: Por ejemplo, acoso escolar, agresión verbal, daños materiales, incumplimiento de normas, etc.\n\n5. **Adjunta evidencias** (opcional pero recomendable): Fotos, videos, mensajes, documentos que respalden tu reporte.\n\n6. **Envía el reporte**: Revisa la información y confirma el envío. Recibirás un número de seguimiento o confirmación.\n\n7. **Seguimiento**: Puedes consultar el estado de tu incidencia en la misma plataforma (en “Mis reportes” o “Consultar incidencias”). Dependiendo de la gravedad, el equipo de convivencia escolar evaluará y tomará acciones.\n\nSi necesitas ayuda para llenar algún campo o no encuentras la opción, indícame tu rol (estudiante, docente, etc.) y te guiaré con más detalle. ¿Tienes alguna otra duda?";	2109271699
laravel-cache-chatbot_q_cdcf8d0131420ccecc2e769f5db62e1b	s:358:"¡Hola! Estoy muy bien, gracias por preguntar. Como TungaBot, estoy listo para ayudarte con cualquier consulta o reporte relacionado con el sistema de incidencias y convivencia escolar. ¿En qué puedo asistirte hoy? ¿Necesitas reportar una incidencia, consultar el estado de alguna, o recibir orientación sobre cómo manejar una situación de convivencia?";	2109273585
laravel-cache-chatbot_q_4d186321c1a7f0f354b297e8914ab240	s:277:"¡Hola! Soy TungaBot, tu asistente virtual para el sistema de incidencias de convivencia escolar. ¿En qué puedo ayudarte hoy? Puedes reportar una incidencia, consultar el estado de un reporte o pedir orientación sobre algún tema de convivencia. Cuéntame, ¿qué necesitas?";	2109273737
laravel-cache-chatbot_q_e718b941e0a7b77601415c6fcc9071bb	s:1454:"Para ingresar una incidencia en el sistema de convivencia escolar, sigue estos pasos:\n\n1. **Accede al sistema**: Ingresa a la plataforma web o aplicación móvil designada por la institución con tus credenciales (usuario y contraseña).\n\n2. **Selecciona la opción "Reportar incidencia"**: En el menú principal, busca y haz clic en la opción para crear un nuevo reporte.\n\n3. **Completa los campos obligatorios**:\n   - **Tipo de incidencia**: Elige la categoría que mejor describa el evento (acoso, agresión, discriminación, etc.).\n   - **Fecha y hora**: Indica cuándo ocurrió el incidente.\n   - **Lugar**: Especifica el espacio donde sucedió (aula, patio, baño, etc.).\n   - **Personas involucradas**: Nombra a los implicados (alumnos, docentes, personal).\n   - **Descripción**: Narra los hechos de manera objetiva, detallada y sin opiniones personales. Incluye solo lo que viste o te contaron directamente.\n   - **Evidencia**: Si tienes pruebas (fotos, videos, mensajes), súbelas al sistema.\n\n4. **Revisa y envía**: Antes de finalizar, verifica que la información sea correcta y completa. Luego, haz clic en "Enviar" o "Guardar".\n\n5. **Confirma el registro**: El sistema te mostrará un número de folio o confirmación. Guárdalo para dar seguimiento.\n\n**Nota**: Si la incidencia es grave o representa un riesgo inminente, contacta directamente a las autoridades escolares o a los servicios de emergencia antes de reportarla en el sistema.";	2109273764
laravel-cache-chatbot_q_a7a7cca761f8b09a9d0285f856457ca1	s:1411:"Hola, soy TungaBot. Para añadir una incidencia al sistema de convivencia escolar, sigue estos pasos:\n\n1. **Accede al módulo de incidencias**: Desde el panel principal de la plataforma educativa, busca la sección "Convivencia" o "Incidencias".\n\n2. **Selecciona "Nueva incidencia"**: Haz clic en el botón o enlace que indique "Añadir incidencia", "Reportar" o similar.\n\n3. **Completa los campos obligatorios**:\n   - **Tipo de incidencia**: Elige entre las categorías predefinidas (por ejemplo: conflicto entre alumnos, falta de respeto, daño material, etc.).\n   - **Fecha y hora**: Especifica cuándo ocurrió el hecho.\n   - **Lugar**: Indica el espacio del centro donde sucedió (aula, patio, pasillo, etc.).\n   - **Alumnos implicados**: Selecciona a los involucrados de la lista de matriculados.\n   - **Descripción**: Redacta un resumen objetivo de los hechos, evitando opiniones personales.\n   - **Testigos** (opcional): Si hay personas que vieron lo ocurrido, menciónalas.\n\n4. **Adjunta evidencias** (si aplica): Puedes subir fotos, documentos o grabaciones que respalden el reporte.\n\n5. **Revisa y envía**: Verifica que todos los datos sean correctos y pulsa "Guardar" o "Enviar".\n\n**Consejo**: Si el sistema requiere autorización de un coordinador, la incidencia quedará en estado "Pendiente" hasta su revisión. Si necesitas ayuda con algún campo en concreto, dímelo y te guiaré paso a paso.";	2109362376
laravel-cache-chatbot_q_49686aebe1ebb3619db20e704eebf560	s:664:"No tengo acceso directo a la base de datos del sistema de incidencias en tiempo real. Sin embargo, puedo orientarte sobre cómo consultar las incidencias pendientes en la plataforma.\n\nPara obtener el número exacto de incidencias pendientes, necesitarías:\n\n1. Iniciar sesión en el sistema de gestión de incidencias.\n2. Ir a la sección de "Incidencias Pendientes" o "Reportes".\n3. Filtrar por estado "Pendiente".\n4. El sistema te mostrará el conteo.\n\nSi no tienes acceso al sistema, contacta con el administrador del sistema de incidencias de la institución educativa.\n\n¿Te gustaría que te guíe paso a paso sobre cómo realizar esta consulta en el sistema?";	2109647363
laravel-cache-chatbot_q_074bfccb492dd1519e52d66023152d64	s:1066:"Hola, soy TungaBot, tu asistente para el sistema de incidencias de la Institución Educativa.\n\nPara responder a tu pregunta, necesito aclarar que no tengo acceso directo a la base de datos en tiempo real para consultar el número exacto de incidencias registradas. Mi función es orientarte sobre cómo reportar, consultar o gestionar incidencias de convivencia escolar.\n\nSi deseas saber cuántas incidencias hay actualmente, te recomiendo:\n1. **Consultar el sistema interno**: Ingresa al módulo de incidencias con tus credenciales de docente o administrador.\n2. **Solicitar un informe**: Puedes pedir un reporte estadístico al área de convivencia escolar.\n3. **Usar filtros**: Si necesitas un tipo específico (ej. académicas, disciplinarias, de convivencia), dime y te guío sobre cómo filtrar los datos.\n\nSi me proporcionas más contexto (como el período, el grado o el tipo de incidencia), puedo ayudarte mejor a interpretar la información que veas en el sistema o a generar una consulta adecuada.\n\n¿Cómo puedo asistirte de manera más específica? 😊";	2109647415
laravel-cache-chatbot_q_2958412bfa6aa9bc0bb578345f799131	s:245:"¡Hola! Soy TungaBot, tu asistente para el sistema de incidencias de convivencia escolar. ¿En qué puedo ayudarte hoy? Puedes reportar una incidencia, consultar el estado de un reporte o pedir orientación sobre algún tema de convivencia. 😊";	2109729448
laravel-cache-chatbot_q_b025526041c04ca101609673b51570ab	s:954:"Claro, aquí tienes la definición de "razón" en español:\n\n**Razón** (del latín *ratio*) es un término polisémico que puede referirse a:\n\n1. **Facultad humana**: Capacidad del ser humano para pensar, comprender, establecer relaciones lógicas, analizar, inferir y actuar de manera coherente. Es la base del conocimiento y la inteligencia.\n\n2. **Argumento o motivo**: Causa o explicación que justifica un hecho, acción o creencia. Ejemplo: "No tengo razón para dudar de ti".\n\n3. **Matemáticas**: Cociente o relación entre dos magnitudes. Se expresa como una fracción o división. Ejemplo: "La razón entre 6 y 3 es 2".\n\n4. **Proporción o correspondencia**: Relación de igualdad entre dos razones. Ejemplo: "La razón de los lados es 2:1".\n\n5. **Derecho o justicia**: Argumento válido o fundamento legal. Ejemplo: "Tiene la razón en su reclamo".\n\nEspero que esta definición te sea útil. Si necesitas algo más específico, házmelo saber.";	2109729498
laravel-cache-chatbot_q_def1a38806523492ad1a33f9b5862cc7	s:1610:"Claro, te explico el significado de "cabro". Esta palabra tiene varios usos dependiendo del contexto:\n\n1. **Literal (animal)**: "Cabro" se refiere a la cría de la cabra, es decir, un caprino joven. En algunas regiones también se usa para designar al macho cabrío adulto.\n\n2. **Coloquial (persona joven)**: En gran parte de Latinoamérica, especialmente en países como Chile, Perú, Colombia y Centroamérica, "cabro" es un término coloquial para referirse a un niño, adolescente o joven. Ejemplo: "Ese cabro es muy aplicado en la escuela". En este sentido, no es ofensivo y se usa de manera cotidiana.\n\n3. **Despectivo o jerga**: Dependiendo del país, "cabro" puede tener connotaciones negativas. Por ejemplo:\n   - En Chile, "cabro" es sinónimo de "niño" o "joven" y es muy común, pero también se usa "cabro chico" para referirse a alguien inmaduro.\n   - En México, "cabro" no es tan común como "cabrón" (que es grosería), pero "cabro" puede usarse para referirse a alguien terco o de mal genio.\n   - En España, "cabro" es casi exclusivamente el animal, aunque en algunas zonas rurales puede usarse como insulto leve.\n\n4. **Otros significados**: En algunas regiones, "cabro" también se usa como sinónimo de "cuckold" (hombre engañado por su pareja), pero esto es más propio de "cabrón" o "cabro" en contextos muy específicos.\n\nSin más contexto, no puedo precisar cuál es la acepción que buscas. Si te refieres al ámbito educativo o de convivencia escolar, probablemente se use en el sentido coloquial de "joven" o "estudiante", pero necesitaría más información para ayudarte mejor.";	2110232611
laravel-cache-chatbot_q_41cbd01c0bc6573a6f884f3fe9e3d650	s:222:"¡Hola! 😊 Qué gusto saludarte. ¿Necesitas ayuda con el sistema de incidencias o tienes alguna consulta sobre convivencia escolar? Estoy aquí para asistirte de manera profesional y clara. ¿En qué puedo ayudarte hoy?";	2110284435
\.


--
-- Data for Name: cache_locks; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.cache_locks (key, owner, expiration) FROM stdin;
\.


--
-- Data for Name: chatbot_conversaciones; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.chatbot_conversaciones (id, usuario_id, created_at, updated_at) FROM stdin;
1	2	2026-11-10 16:00:18	2026-11-10 16:00:18
2	4	2026-11-10 22:57:28	2026-11-10 22:57:28
3	4	2026-11-16 18:43:31	2026-11-16 18:43:31
4	2	2026-11-17 09:07:15	2026-11-17 09:07:15
\.


--
-- Data for Name: chatbot_mensajes; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.chatbot_mensajes (id, conversacion_id, emisor, contenido, estado, created_at, updated_at) FROM stdin;
1	1	usuario	hola	completado	2026-11-10 16:00:18	2026-11-10 16:00:18
2	1	asistente	¡Hola! Soy TungaBot, tu asistente virtual para el sistema de incidencias de convivencia escolar. ¿En qué puedo ayudarte hoy? Puedes reportar una incidencia, consultar el estado de un reporte o pedir orientación sobre algún tema de convivencia. Cuéntame, ¿qué necesitas?	completado	2026-11-10 16:00:18	2026-11-10 16:00:18
3	2	usuario	hols	completado	2026-11-10 22:57:28	2026-11-10 22:57:28
4	2	asistente	¡Hola! Soy TungaBot, tu asistente para el sistema de incidencias de convivencia escolar. ¿En qué puedo ayudarte hoy? Puedes reportar una incidencia, consultar el estado de un reporte o pedir orientación sobre algún tema de convivencia. 😊	completado	2026-11-10 22:57:28	2026-11-10 22:57:28
5	2	usuario	dame la definicion de razon	completado	2026-11-10 22:58:18	2026-11-10 22:58:18
6	2	asistente	Claro, aquí tienes la definición de "razón" en español:\n\n**Razón** (del latín *ratio*) es un término polisémico que puede referirse a:\n\n1. **Facultad humana**: Capacidad del ser humano para pensar, comprender, establecer relaciones lógicas, analizar, inferir y actuar de manera coherente. Es la base del conocimiento y la inteligencia.\n\n2. **Argumento o motivo**: Causa o explicación que justifica un hecho, acción o creencia. Ejemplo: "No tengo razón para dudar de ti".\n\n3. **Matemáticas**: Cociente o relación entre dos magnitudes. Se expresa como una fracción o división. Ejemplo: "La razón entre 6 y 3 es 2".\n\n4. **Proporción o correspondencia**: Relación de igualdad entre dos razones. Ejemplo: "La razón de los lados es 2:1".\n\n5. **Derecho o justicia**: Argumento válido o fundamento legal. Ejemplo: "Tiene la razón en su reclamo".\n\nEspero que esta definición te sea útil. Si necesitas algo más específico, házmelo saber.	completado	2026-11-10 22:58:18	2026-11-10 22:58:18
7	3	usuario	define que es cabro	completado	2026-11-16 18:43:31	2026-11-16 18:43:31
8	3	asistente	Claro, te explico el significado de "cabro". Esta palabra tiene varios usos dependiendo del contexto:\n\n1. **Literal (animal)**: "Cabro" se refiere a la cría de la cabra, es decir, un caprino joven. En algunas regiones también se usa para designar al macho cabrío adulto.\n\n2. **Coloquial (persona joven)**: En gran parte de Latinoamérica, especialmente en países como Chile, Perú, Colombia y Centroamérica, "cabro" es un término coloquial para referirse a un niño, adolescente o joven. Ejemplo: "Ese cabro es muy aplicado en la escuela". En este sentido, no es ofensivo y se usa de manera cotidiana.\n\n3. **Despectivo o jerga**: Dependiendo del país, "cabro" puede tener connotaciones negativas. Por ejemplo:\n   - En Chile, "cabro" es sinónimo de "niño" o "joven" y es muy común, pero también se usa "cabro chico" para referirse a alguien inmaduro.\n   - En México, "cabro" no es tan común como "cabrón" (que es grosería), pero "cabro" puede usarse para referirse a alguien terco o de mal genio.\n   - En España, "cabro" es casi exclusivamente el animal, aunque en algunas zonas rurales puede usarse como insulto leve.\n\n4. **Otros significados**: En algunas regiones, "cabro" también se usa como sinónimo de "cuckold" (hombre engañado por su pareja), pero esto es más propio de "cabrón" o "cabro" en contextos muy específicos.\n\nSin más contexto, no puedo precisar cuál es la acepción que buscas. Si te refieres al ámbito educativo o de convivencia escolar, probablemente se use en el sentido coloquial de "joven" o "estudiante", pero necesitaría más información para ayudarte mejor.	completado	2026-11-16 18:43:31	2026-11-16 18:43:31
9	4	usuario	hola bebe	completado	2026-11-17 09:07:15	2026-11-17 09:07:15
10	4	asistente	¡Hola! 😊 Qué gusto saludarte. ¿Necesitas ayuda con el sistema de incidencias o tienes alguna consulta sobre convivencia escolar? Estoy aquí para asistirte de manera profesional y clara. ¿En qué puedo ayudarte hoy?	completado	2026-11-17 09:07:15	2026-11-17 09:07:15
\.


--
-- Data for Name: cursos; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.cursos (id, nombre) FROM stdin;
1	PRUEBA_DASHBOARD_Matemática
2	Matemática
3	Comunicación
4	Ciencias Sociales
5	Ciencia y Tecnología
6	Educación Física
7	Educación Religiosa
8	Inglés
9	Educación para el Trabajo
10	Comunicación (Taller)
11	Matemática (Taller)
12	Inglés (Taller)
13	Educación para el Trabajo (Taller)
14	AIP
15	PRUEBA_DASHBOARD_ADMIN_teoria_Matemática
16	PRUEBA_DASHBOARD_ADMIN_teoria_Comunicación
17	PRUEBA_DASHBOARD_ADMIN_teoria_Ciencias Sociales
18	PRUEBA_DASHBOARD_ADMIN_teoria_Religión
19	PRUEBA_DASHBOARD_ADMIN_teoria_Educación Física
20	PRUEBA_DASHBOARD_ADMIN_taller_Matemática
21	PRUEBA_DASHBOARD_ADMIN_taller_Comunicación
22	PRUEBA_DASHBOARD_ADMIN_taller_Ciencias Sociales
23	PRUEBA_DASHBOARD_ADMIN_taller_Religión
24	PRUEBA_DASHBOARD_ADMIN_taller_Educación Física
\.


--
-- Data for Name: estados; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.estados (id, nombre) FROM stdin;
1	Pendiente
2	En Proceso
3	Finalizado
8	En proceso
\.


--
-- Data for Name: estudiantes; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.estudiantes (id, dni, nombre, seccion_id) FROM stdin;
1	88880001	PRUEBA_Alumno Uno	1
2	88880002	PRUEBA_Alumno Dos	1
3	88880003	PRUEBA_Alumno Tres	2
4	12345678	elmer	1
5	88991001	PRUEBA_DASHBOARD_ADMIN_Alumno_0	3
6	88991002	PRUEBA_DASHBOARD_ADMIN_Alumno_1	4
7	88991003	PRUEBA_DASHBOARD_ADMIN_Alumno_2	5
8	88991004	PRUEBA_DASHBOARD_ADMIN_Alumno_3	6
9	88991005	PRUEBA_DASHBOARD_ADMIN_Alumno_4	7
\.


--
-- Data for Name: failed_jobs; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.failed_jobs (id, uuid, connection, queue, payload, exception, failed_at) FROM stdin;
\.


--
-- Data for Name: gravedades; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.gravedades (id, nombre) FROM stdin;
1	Leve
2	Intermedia
3	Grave
\.


--
-- Data for Name: incidencia_fotografias; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.incidencia_fotografias (id, incidencia_id, ruta_archivo, nombre_original, tipo_mime, tamano, created_at, updated_at, deleted_at, eliminado_por) FROM stdin;
1	164	incidencias/6de6a347-0d6d-4d62-8962-3ec82411448a.jpg	fotografia-20261111233527083.jpg	image/jpeg	43340	2026-11-11 18:35:41	2026-11-11 18:35:41	\N	\N
\.


--
-- Data for Name: incidencia_tipo; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.incidencia_tipo (incidencia_id, tipo_incidencia_id) FROM stdin;
161	3
161	5
162	3
162	4
163	3
163	1
164	3
164	4
164	10
165	3
165	4
\.


--
-- Data for Name: incidencias; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.incidencias (id, estudiante_id, usuario_id, curso_id, fecha, hora, descripcion, gravedad_id, estado_id, tipo_aula, deleted_at, eliminado_por) FROM stdin;
1	1	2	1	2026-11-09	08:00:00	PRUEBA_DASHBOARD_ Conducta inadecuada en clase	1	1	\N	\N	\N
2	2	2	1	2026-11-10	08:00:00	PRUEBA_DASHBOARD_ Falta de respeto al docente	2	1	\N	\N	\N
3	3	2	1	2026-11-11	08:00:00	PRUEBA_DASHBOARD_ Agresión física a compañero	3	1	\N	\N	\N
4	1	2	1	2026-11-10	08:00:00	PRUEBA_DASHBOARD_ Llegó tarde a clases	1	1	\N	\N	\N
5	2	2	1	2026-11-02	08:00:00	PRUEBA_DASHBOARD_ No trajo materiales	2	1	\N	\N	\N
6	3	2	1	2026-11-03	08:00:00	PRUEBA_DASHBOARD_ Uso de celular en clase	1	1	\N	\N	\N
8	5	3	15	2026-11-02	08:00:00	PRUEBA_DASHBOARD_ADMIN_teoria_0_1	2	1	teoria	\N	\N
9	5	3	15	2026-11-09	08:00:00	PRUEBA_DASHBOARD_ADMIN_teoria_0_2	3	1	teoria	\N	\N
10	5	3	15	2026-11-04	08:00:00	PRUEBA_DASHBOARD_ADMIN_teoria_0_3	1	1	teoria	\N	\N
12	5	3	15	2026-11-06	08:00:00	PRUEBA_DASHBOARD_ADMIN_teoria_0_5	3	1	teoria	\N	\N
13	5	3	15	2026-11-09	08:00:00	PRUEBA_DASHBOARD_ADMIN_teoria_0_6	1	1	teoria	\N	\N
14	5	3	15	2026-11-08	08:00:00	PRUEBA_DASHBOARD_ADMIN_teoria_0_7	2	1	teoria	\N	\N
15	5	3	15	2026-11-09	08:00:00	PRUEBA_DASHBOARD_ADMIN_teoria_0_8	3	1	teoria	\N	\N
16	5	3	15	2026-11-02	08:00:00	PRUEBA_DASHBOARD_ADMIN_teoria_0_9	1	1	teoria	\N	\N
17	6	3	16	2026-11-09	08:00:00	PRUEBA_DASHBOARD_ADMIN_teoria_1_0	1	1	teoria	\N	\N
18	6	3	16	2026-11-02	08:00:00	PRUEBA_DASHBOARD_ADMIN_teoria_1_1	2	1	teoria	\N	\N
19	6	3	16	2026-11-09	08:00:00	PRUEBA_DASHBOARD_ADMIN_teoria_1_2	3	1	teoria	\N	\N
20	6	3	16	2026-11-04	08:00:00	PRUEBA_DASHBOARD_ADMIN_teoria_1_3	1	1	teoria	\N	\N
21	6	3	16	2026-11-09	08:00:00	PRUEBA_DASHBOARD_ADMIN_teoria_1_4	2	1	teoria	\N	\N
22	6	3	16	2026-11-06	08:00:00	PRUEBA_DASHBOARD_ADMIN_teoria_1_5	3	1	teoria	\N	\N
23	6	3	16	2026-11-09	08:00:00	PRUEBA_DASHBOARD_ADMIN_teoria_1_6	1	1	teoria	\N	\N
24	6	3	16	2026-11-08	08:00:00	PRUEBA_DASHBOARD_ADMIN_teoria_1_7	2	1	teoria	\N	\N
25	6	3	16	2026-11-09	08:00:00	PRUEBA_DASHBOARD_ADMIN_teoria_1_8	3	1	teoria	\N	\N
26	6	3	16	2026-11-02	08:00:00	PRUEBA_DASHBOARD_ADMIN_teoria_1_9	1	1	teoria	\N	\N
27	6	3	16	2026-11-09	08:00:00	PRUEBA_DASHBOARD_ADMIN_teoria_1_10	2	1	teoria	\N	\N
28	6	3	16	2026-11-04	08:00:00	PRUEBA_DASHBOARD_ADMIN_teoria_1_11	3	1	teoria	\N	\N
29	6	3	16	2026-11-09	08:00:00	PRUEBA_DASHBOARD_ADMIN_teoria_1_12	1	1	teoria	\N	\N
30	6	3	16	2026-11-06	08:00:00	PRUEBA_DASHBOARD_ADMIN_teoria_1_13	2	1	teoria	\N	\N
31	6	3	16	2026-11-09	08:00:00	PRUEBA_DASHBOARD_ADMIN_teoria_1_14	3	1	teoria	\N	\N
32	6	3	16	2026-11-08	08:00:00	PRUEBA_DASHBOARD_ADMIN_teoria_1_15	1	1	teoria	\N	\N
33	6	3	16	2026-11-09	08:00:00	PRUEBA_DASHBOARD_ADMIN_teoria_1_16	2	1	teoria	\N	\N
34	6	3	16	2026-11-02	08:00:00	PRUEBA_DASHBOARD_ADMIN_teoria_1_17	3	1	teoria	\N	\N
35	6	3	16	2026-11-09	08:00:00	PRUEBA_DASHBOARD_ADMIN_teoria_1_18	1	1	teoria	\N	\N
36	6	3	16	2026-11-04	08:00:00	PRUEBA_DASHBOARD_ADMIN_teoria_1_19	2	1	teoria	\N	\N
37	6	3	16	2026-11-09	08:00:00	PRUEBA_DASHBOARD_ADMIN_teoria_1_20	3	1	teoria	\N	\N
38	6	3	16	2026-11-06	08:00:00	PRUEBA_DASHBOARD_ADMIN_teoria_1_21	1	1	teoria	\N	\N
39	6	3	16	2026-11-09	08:00:00	PRUEBA_DASHBOARD_ADMIN_teoria_1_22	2	1	teoria	\N	\N
40	6	3	16	2026-11-08	08:00:00	PRUEBA_DASHBOARD_ADMIN_teoria_1_23	3	1	teoria	\N	\N
41	6	3	16	2026-11-09	08:00:00	PRUEBA_DASHBOARD_ADMIN_teoria_1_24	1	1	teoria	\N	\N
42	6	3	16	2026-11-02	08:00:00	PRUEBA_DASHBOARD_ADMIN_teoria_1_25	2	1	teoria	\N	\N
43	6	3	16	2026-11-09	08:00:00	PRUEBA_DASHBOARD_ADMIN_teoria_1_26	3	1	teoria	\N	\N
44	6	3	16	2026-11-04	08:00:00	PRUEBA_DASHBOARD_ADMIN_teoria_1_27	1	1	teoria	\N	\N
45	6	3	16	2026-11-09	08:00:00	PRUEBA_DASHBOARD_ADMIN_teoria_1_28	2	1	teoria	\N	\N
46	6	3	16	2026-11-06	08:00:00	PRUEBA_DASHBOARD_ADMIN_teoria_1_29	3	1	teoria	\N	\N
47	7	3	17	2026-11-09	08:00:00	PRUEBA_DASHBOARD_ADMIN_teoria_2_0	1	1	teoria	\N	\N
48	7	3	17	2026-11-02	08:00:00	PRUEBA_DASHBOARD_ADMIN_teoria_2_1	2	1	teoria	\N	\N
49	7	3	17	2026-11-09	08:00:00	PRUEBA_DASHBOARD_ADMIN_teoria_2_2	3	1	teoria	\N	\N
50	7	3	17	2026-11-04	08:00:00	PRUEBA_DASHBOARD_ADMIN_teoria_2_3	1	1	teoria	\N	\N
51	7	3	17	2026-11-09	08:00:00	PRUEBA_DASHBOARD_ADMIN_teoria_2_4	2	1	teoria	\N	\N
52	7	3	17	2026-11-06	08:00:00	PRUEBA_DASHBOARD_ADMIN_teoria_2_5	3	1	teoria	\N	\N
53	7	3	17	2026-11-09	08:00:00	PRUEBA_DASHBOARD_ADMIN_teoria_2_6	1	1	teoria	\N	\N
54	7	3	17	2026-11-08	08:00:00	PRUEBA_DASHBOARD_ADMIN_teoria_2_7	2	1	teoria	\N	\N
55	7	3	17	2026-11-09	08:00:00	PRUEBA_DASHBOARD_ADMIN_teoria_2_8	3	1	teoria	\N	\N
56	7	3	17	2026-11-02	08:00:00	PRUEBA_DASHBOARD_ADMIN_teoria_2_9	1	1	teoria	\N	\N
57	7	3	17	2026-11-09	08:00:00	PRUEBA_DASHBOARD_ADMIN_teoria_2_10	2	1	teoria	\N	\N
58	7	3	17	2026-11-04	08:00:00	PRUEBA_DASHBOARD_ADMIN_teoria_2_11	3	1	teoria	\N	\N
59	7	3	17	2026-11-09	08:00:00	PRUEBA_DASHBOARD_ADMIN_teoria_2_12	1	1	teoria	\N	\N
60	7	3	17	2026-11-06	08:00:00	PRUEBA_DASHBOARD_ADMIN_teoria_2_13	2	1	teoria	\N	\N
61	7	3	17	2026-11-09	08:00:00	PRUEBA_DASHBOARD_ADMIN_teoria_2_14	3	1	teoria	\N	\N
62	7	3	17	2026-11-08	08:00:00	PRUEBA_DASHBOARD_ADMIN_teoria_2_15	1	1	teoria	\N	\N
63	7	3	17	2026-11-09	08:00:00	PRUEBA_DASHBOARD_ADMIN_teoria_2_16	2	1	teoria	\N	\N
64	7	3	17	2026-11-02	08:00:00	PRUEBA_DASHBOARD_ADMIN_teoria_2_17	3	1	teoria	\N	\N
65	8	3	18	2026-11-09	08:00:00	PRUEBA_DASHBOARD_ADMIN_teoria_3_0	1	1	teoria	\N	\N
66	8	3	18	2026-11-02	08:00:00	PRUEBA_DASHBOARD_ADMIN_teoria_3_1	2	1	teoria	\N	\N
67	8	3	18	2026-11-09	08:00:00	PRUEBA_DASHBOARD_ADMIN_teoria_3_2	3	1	teoria	\N	\N
68	8	3	18	2026-11-04	08:00:00	PRUEBA_DASHBOARD_ADMIN_teoria_3_3	1	1	teoria	\N	\N
69	8	3	18	2026-11-09	08:00:00	PRUEBA_DASHBOARD_ADMIN_teoria_3_4	2	1	teoria	\N	\N
70	8	3	18	2026-11-06	08:00:00	PRUEBA_DASHBOARD_ADMIN_teoria_3_5	3	1	teoria	\N	\N
71	8	3	18	2026-11-09	08:00:00	PRUEBA_DASHBOARD_ADMIN_teoria_3_6	1	1	teoria	\N	\N
72	8	3	18	2026-11-08	08:00:00	PRUEBA_DASHBOARD_ADMIN_teoria_3_7	2	1	teoria	\N	\N
73	8	3	18	2026-11-09	08:00:00	PRUEBA_DASHBOARD_ADMIN_teoria_3_8	3	1	teoria	\N	\N
74	8	3	18	2026-11-02	08:00:00	PRUEBA_DASHBOARD_ADMIN_teoria_3_9	1	1	teoria	\N	\N
75	8	3	18	2026-11-09	08:00:00	PRUEBA_DASHBOARD_ADMIN_teoria_3_10	2	1	teoria	\N	\N
76	8	3	18	2026-11-04	08:00:00	PRUEBA_DASHBOARD_ADMIN_teoria_3_11	3	1	teoria	\N	\N
77	8	3	18	2026-11-09	08:00:00	PRUEBA_DASHBOARD_ADMIN_teoria_3_12	1	1	teoria	\N	\N
78	8	3	18	2026-11-06	08:00:00	PRUEBA_DASHBOARD_ADMIN_teoria_3_13	2	1	teoria	\N	\N
79	8	3	18	2026-11-09	08:00:00	PRUEBA_DASHBOARD_ADMIN_teoria_3_14	3	1	teoria	\N	\N
80	9	3	19	2026-11-09	08:00:00	PRUEBA_DASHBOARD_ADMIN_teoria_4_0	1	1	teoria	\N	\N
81	9	3	19	2026-11-02	08:00:00	PRUEBA_DASHBOARD_ADMIN_teoria_4_1	2	1	teoria	\N	\N
82	9	3	19	2026-11-09	08:00:00	PRUEBA_DASHBOARD_ADMIN_teoria_4_2	3	1	teoria	\N	\N
83	9	3	19	2026-11-04	08:00:00	PRUEBA_DASHBOARD_ADMIN_teoria_4_3	1	1	teoria	\N	\N
84	5	3	20	2026-11-09	08:00:00	PRUEBA_DASHBOARD_ADMIN_taller_0_0	1	1	taller	\N	\N
85	5	3	20	2026-11-02	08:00:00	PRUEBA_DASHBOARD_ADMIN_taller_0_1	2	1	taller	\N	\N
86	5	3	20	2026-11-09	08:00:00	PRUEBA_DASHBOARD_ADMIN_taller_0_2	3	1	taller	\N	\N
87	5	3	20	2026-11-04	08:00:00	PRUEBA_DASHBOARD_ADMIN_taller_0_3	1	1	taller	\N	\N
88	5	3	20	2026-11-09	08:00:00	PRUEBA_DASHBOARD_ADMIN_taller_0_4	2	1	taller	\N	\N
89	5	3	20	2026-11-06	08:00:00	PRUEBA_DASHBOARD_ADMIN_taller_0_5	3	1	taller	\N	\N
90	5	3	20	2026-11-09	08:00:00	PRUEBA_DASHBOARD_ADMIN_taller_0_6	1	1	taller	\N	\N
91	5	3	20	2026-11-08	08:00:00	PRUEBA_DASHBOARD_ADMIN_taller_0_7	2	1	taller	\N	\N
92	5	3	20	2026-11-09	08:00:00	PRUEBA_DASHBOARD_ADMIN_taller_0_8	3	1	taller	\N	\N
93	5	3	20	2026-11-02	08:00:00	PRUEBA_DASHBOARD_ADMIN_taller_0_9	1	1	taller	\N	\N
94	6	3	21	2026-11-09	08:00:00	PRUEBA_DASHBOARD_ADMIN_taller_1_0	1	1	taller	\N	\N
95	6	3	21	2026-11-02	08:00:00	PRUEBA_DASHBOARD_ADMIN_taller_1_1	2	1	taller	\N	\N
96	6	3	21	2026-11-09	08:00:00	PRUEBA_DASHBOARD_ADMIN_taller_1_2	3	1	taller	\N	\N
97	6	3	21	2026-11-04	08:00:00	PRUEBA_DASHBOARD_ADMIN_taller_1_3	1	1	taller	\N	\N
98	6	3	21	2026-11-09	08:00:00	PRUEBA_DASHBOARD_ADMIN_taller_1_4	2	1	taller	\N	\N
99	6	3	21	2026-11-06	08:00:00	PRUEBA_DASHBOARD_ADMIN_taller_1_5	3	1	taller	\N	\N
100	6	3	21	2026-11-09	08:00:00	PRUEBA_DASHBOARD_ADMIN_taller_1_6	1	1	taller	\N	\N
101	6	3	21	2026-11-08	08:00:00	PRUEBA_DASHBOARD_ADMIN_taller_1_7	2	1	taller	\N	\N
102	6	3	21	2026-11-09	08:00:00	PRUEBA_DASHBOARD_ADMIN_taller_1_8	3	1	taller	\N	\N
103	6	3	21	2026-11-02	08:00:00	PRUEBA_DASHBOARD_ADMIN_taller_1_9	1	1	taller	\N	\N
104	6	3	21	2026-11-09	08:00:00	PRUEBA_DASHBOARD_ADMIN_taller_1_10	2	1	taller	\N	\N
105	6	3	21	2026-11-04	08:00:00	PRUEBA_DASHBOARD_ADMIN_taller_1_11	3	1	taller	\N	\N
106	6	3	21	2026-11-09	08:00:00	PRUEBA_DASHBOARD_ADMIN_taller_1_12	1	1	taller	\N	\N
107	6	3	21	2026-11-06	08:00:00	PRUEBA_DASHBOARD_ADMIN_taller_1_13	2	1	taller	\N	\N
108	6	3	21	2026-11-09	08:00:00	PRUEBA_DASHBOARD_ADMIN_taller_1_14	3	1	taller	\N	\N
109	6	3	21	2026-11-08	08:00:00	PRUEBA_DASHBOARD_ADMIN_taller_1_15	1	1	taller	\N	\N
110	6	3	21	2026-11-09	08:00:00	PRUEBA_DASHBOARD_ADMIN_taller_1_16	2	1	taller	\N	\N
111	6	3	21	2026-11-02	08:00:00	PRUEBA_DASHBOARD_ADMIN_taller_1_17	3	1	taller	\N	\N
112	6	3	21	2026-11-09	08:00:00	PRUEBA_DASHBOARD_ADMIN_taller_1_18	1	1	taller	\N	\N
113	6	3	21	2026-11-04	08:00:00	PRUEBA_DASHBOARD_ADMIN_taller_1_19	2	1	taller	\N	\N
114	6	3	21	2026-11-09	08:00:00	PRUEBA_DASHBOARD_ADMIN_taller_1_20	3	1	taller	\N	\N
115	6	3	21	2026-11-06	08:00:00	PRUEBA_DASHBOARD_ADMIN_taller_1_21	1	1	taller	\N	\N
116	6	3	21	2026-11-09	08:00:00	PRUEBA_DASHBOARD_ADMIN_taller_1_22	2	1	taller	\N	\N
117	6	3	21	2026-11-08	08:00:00	PRUEBA_DASHBOARD_ADMIN_taller_1_23	3	1	taller	\N	\N
118	6	3	21	2026-11-09	08:00:00	PRUEBA_DASHBOARD_ADMIN_taller_1_24	1	1	taller	\N	\N
119	6	3	21	2026-11-02	08:00:00	PRUEBA_DASHBOARD_ADMIN_taller_1_25	2	1	taller	\N	\N
120	6	3	21	2026-11-09	08:00:00	PRUEBA_DASHBOARD_ADMIN_taller_1_26	3	1	taller	\N	\N
121	6	3	21	2026-11-04	08:00:00	PRUEBA_DASHBOARD_ADMIN_taller_1_27	1	1	taller	\N	\N
122	6	3	21	2026-11-09	08:00:00	PRUEBA_DASHBOARD_ADMIN_taller_1_28	2	1	taller	\N	\N
123	6	3	21	2026-11-06	08:00:00	PRUEBA_DASHBOARD_ADMIN_taller_1_29	3	1	taller	\N	\N
124	7	3	22	2026-11-09	08:00:00	PRUEBA_DASHBOARD_ADMIN_taller_2_0	1	1	taller	\N	\N
125	7	3	22	2026-11-02	08:00:00	PRUEBA_DASHBOARD_ADMIN_taller_2_1	2	1	taller	\N	\N
126	7	3	22	2026-11-09	08:00:00	PRUEBA_DASHBOARD_ADMIN_taller_2_2	3	1	taller	\N	\N
127	7	3	22	2026-11-04	08:00:00	PRUEBA_DASHBOARD_ADMIN_taller_2_3	1	1	taller	\N	\N
128	7	3	22	2026-11-09	08:00:00	PRUEBA_DASHBOARD_ADMIN_taller_2_4	2	1	taller	\N	\N
129	7	3	22	2026-11-06	08:00:00	PRUEBA_DASHBOARD_ADMIN_taller_2_5	3	1	taller	\N	\N
130	7	3	22	2026-11-09	08:00:00	PRUEBA_DASHBOARD_ADMIN_taller_2_6	1	1	taller	\N	\N
131	7	3	22	2026-11-08	08:00:00	PRUEBA_DASHBOARD_ADMIN_taller_2_7	2	1	taller	\N	\N
132	7	3	22	2026-11-09	08:00:00	PRUEBA_DASHBOARD_ADMIN_taller_2_8	3	1	taller	\N	\N
133	7	3	22	2026-11-02	08:00:00	PRUEBA_DASHBOARD_ADMIN_taller_2_9	1	1	taller	\N	\N
134	7	3	22	2026-11-09	08:00:00	PRUEBA_DASHBOARD_ADMIN_taller_2_10	2	1	taller	\N	\N
135	7	3	22	2026-11-04	08:00:00	PRUEBA_DASHBOARD_ADMIN_taller_2_11	3	1	taller	\N	\N
136	7	3	22	2026-11-09	08:00:00	PRUEBA_DASHBOARD_ADMIN_taller_2_12	1	1	taller	\N	\N
137	7	3	22	2026-11-06	08:00:00	PRUEBA_DASHBOARD_ADMIN_taller_2_13	2	1	taller	\N	\N
138	7	3	22	2026-11-09	08:00:00	PRUEBA_DASHBOARD_ADMIN_taller_2_14	3	1	taller	\N	\N
139	7	3	22	2026-11-08	08:00:00	PRUEBA_DASHBOARD_ADMIN_taller_2_15	1	1	taller	\N	\N
140	7	3	22	2026-11-09	08:00:00	PRUEBA_DASHBOARD_ADMIN_taller_2_16	2	1	taller	\N	\N
141	7	3	22	2026-11-02	08:00:00	PRUEBA_DASHBOARD_ADMIN_taller_2_17	3	1	taller	\N	\N
142	8	3	23	2026-11-09	08:00:00	PRUEBA_DASHBOARD_ADMIN_taller_3_0	1	1	taller	\N	\N
143	8	3	23	2026-11-02	08:00:00	PRUEBA_DASHBOARD_ADMIN_taller_3_1	2	1	taller	\N	\N
144	8	3	23	2026-11-09	08:00:00	PRUEBA_DASHBOARD_ADMIN_taller_3_2	3	1	taller	\N	\N
145	8	3	23	2026-11-04	08:00:00	PRUEBA_DASHBOARD_ADMIN_taller_3_3	1	1	taller	\N	\N
146	8	3	23	2026-11-09	08:00:00	PRUEBA_DASHBOARD_ADMIN_taller_3_4	2	1	taller	\N	\N
147	8	3	23	2026-11-06	08:00:00	PRUEBA_DASHBOARD_ADMIN_taller_3_5	3	1	taller	\N	\N
148	8	3	23	2026-11-09	08:00:00	PRUEBA_DASHBOARD_ADMIN_taller_3_6	1	1	taller	\N	\N
149	8	3	23	2026-11-08	08:00:00	PRUEBA_DASHBOARD_ADMIN_taller_3_7	2	1	taller	\N	\N
150	8	3	23	2026-11-09	08:00:00	PRUEBA_DASHBOARD_ADMIN_taller_3_8	3	1	taller	\N	\N
151	8	3	23	2026-11-02	08:00:00	PRUEBA_DASHBOARD_ADMIN_taller_3_9	1	1	taller	\N	\N
152	8	3	23	2026-11-09	08:00:00	PRUEBA_DASHBOARD_ADMIN_taller_3_10	2	1	taller	\N	\N
153	8	3	23	2026-11-04	08:00:00	PRUEBA_DASHBOARD_ADMIN_taller_3_11	3	1	taller	\N	\N
154	8	3	23	2026-11-09	08:00:00	PRUEBA_DASHBOARD_ADMIN_taller_3_12	1	1	taller	\N	\N
155	8	3	23	2026-11-06	08:00:00	PRUEBA_DASHBOARD_ADMIN_taller_3_13	2	1	taller	\N	\N
156	8	3	23	2026-11-09	08:00:00	PRUEBA_DASHBOARD_ADMIN_taller_3_14	3	1	taller	\N	\N
157	9	3	24	2026-11-09	08:00:00	PRUEBA_DASHBOARD_ADMIN_taller_4_0	1	1	taller	\N	\N
158	9	3	24	2026-11-02	08:00:00	PRUEBA_DASHBOARD_ADMIN_taller_4_1	2	1	taller	\N	\N
159	9	3	24	2026-11-09	08:00:00	PRUEBA_DASHBOARD_ADMIN_taller_4_2	3	1	taller	\N	\N
160	9	3	24	2026-11-04	08:00:00	PRUEBA_DASHBOARD_ADMIN_taller_4_3	1	1	taller	\N	\N
161	4	2	1	2026-11-10	22:23:14	nada	1	1	teoria	\N	\N
162	4	2	1	2026-11-11	14:49:43	jj	2	1	teoria	\N	\N
163	4	8	2	2026-11-11	16:55:07	erty	1	8	teoria	\N	\N
164	4	8	2	2026-11-11	18:35:40	sonsooo	2	2	taller	\N	\N
165	3	4	8	2026-11-16	19:36:10	aa	1	2	teoria	\N	\N
7	5	3	15	2026-11-09	08:00:00	PRUEBA_DASHBOARD_ADMIN_teoria_0_0	1	1	teoria	2026-11-16 19:36:57	4
11	5	3	15	2026-11-09	08:00:00	PRUEBA_DASHBOARD_ADMIN_teoria_0_4	2	1	teoria	2026-11-16 19:37:05	4
166	1	2	1	2026-11-16	08:00:00	PRUEBA_DASHBOARD_ Conducta inadecuada en clase	1	1	\N	\N	\N
167	2	2	1	2026-11-17	08:00:00	PRUEBA_DASHBOARD_ Falta de respeto al docente	2	1	\N	\N	\N
168	3	2	1	2026-11-18	08:00:00	PRUEBA_DASHBOARD_ Agresión física a compañero	3	1	\N	\N	\N
169	1	2	1	2026-11-17	08:00:00	PRUEBA_DASHBOARD_ Llegó tarde a clases	1	1	\N	\N	\N
170	2	2	1	2026-11-09	08:00:00	PRUEBA_DASHBOARD_ No trajo materiales	2	1	\N	\N	\N
171	3	2	1	2026-11-10	08:00:00	PRUEBA_DASHBOARD_ Uso de celular en clase	1	1	\N	\N	\N
172	1	2	1	2026-11-02	08:00:00	PRUEBA_DASHBOARD_ Amenazas a un compañero	3	1	\N	\N	\N
\.


--
-- Data for Name: job_batches; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.job_batches (id, name, total_jobs, pending_jobs, failed_jobs, failed_job_ids, options, cancelled_at, created_at, finished_at) FROM stdin;
\.


--
-- Data for Name: jobs; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.jobs (id, queue, payload, attempts, reserved_at, available_at, created_at) FROM stdin;
\.


--
-- Data for Name: migrations; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.migrations (id, migration, batch) FROM stdin;
1	0001_01_01_000000_create_users_table	1
2	0001_01_01_000001_create_cache_table	1
3	0001_01_01_000002_create_jobs_table	1
4	2026_09_28_030000_create_secciones_table	1
5	2026_09_28_030001_create_roles_table	1
6	2026_09_28_030002_create_cursos_table	1
7	2026_09_28_030003_create_estudiantes_table	1
8	2026_09_30_161238_create_gravedades_table	1
9	2026_09_30_161333_create_estados_table	1
10	2026_09_30_161400_create_incidencias_table	1
11	2026_10_07_023408_add_role_and_account_status_to_users_table	2
12	2026_10_07_032915_create_asignaciones_docentes_table	3
13	2026_10_07_042117_update_incidencias_structure	4
14	2026_10_13_231500_create_tipos_incidencia_table	5
15	2026_10_13_231501_create_incidencia_tipo_table	5
16	2026_10_14_090000_add_tipo_aula_to_incidencias_table	6
17	2026_10_14_091000_create_incidencia_fotografias_table	6
18	2026_10_14_231200_add_soft_deletes_to_incidencia_fotografias_table	6
19	2026_10_14_231800_add_soft_deletes_to_incidencias_table	6
20	2026_11_03_180000_create_chatbot_conversaciones_table	7
21	2026_11_03_180001_create_chatbot_mensajes_table	8
22	2026_11_11_000000_add_grado_curso_to_users_table	9
\.


--
-- Data for Name: password_reset_tokens; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.password_reset_tokens (email, token, created_at) FROM stdin;
\.


--
-- Data for Name: roles; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.roles (id, nombre_rol) FROM stdin;
1	Administrador
2	Biblioteca Escolar
3	Mobiliario y Aulas
4	Equipos TIC
5	Aula AIP
6	Profesor
7	Directora
8	Subdirector
9	Administrador
10	Profesor
11	Directora
12	Subdirector
\.


--
-- Data for Name: secciones; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.secciones (id, grado, seccion) FROM stdin;
1	PRUEBA_1ro	A
2	PRUEBA_1ro	B
3	PRUEBA_DASHBOARD_ADMIN_1 grado	A
4	PRUEBA_DASHBOARD_ADMIN_2 grado	A
5	PRUEBA_DASHBOARD_ADMIN_3 grado	A
6	PRUEBA_DASHBOARD_ADMIN_4 grado	A
7	PRUEBA_DASHBOARD_ADMIN_5 grado	A
8	3	b
9	PRUEBA_1ro	f
\.


--
-- Data for Name: sessions; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.sessions (id, user_id, ip_address, user_agent, payload, last_activity) FROM stdin;
quwiKpcW7QNkGPVnzwGiltH5AK0iAlEahqkKpemn	\N	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/157.0.0.0 Safari/537.36	YToyOntzOjY6Il90b2tlbiI7czo0MDoiMkZHd1VseHVtRVhlT0VJQkJoUlVlNG1DM3JZaVpDbjdLd3BnenJRNyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==	1794872486
QVomZs1xxOBxVypi1TNyZyOepH4OefJcWFQ6dmMJ	\N	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/157.0.0.0 Safari/537.36	YTo5OntzOjY6Il90b2tlbiI7czo0MDoiWjVtTXpvSkJsUXdzQUFaNmpSb3VkRkRiMkQzSDV6UHlXNGtuSFVyRyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzM6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9lc3R1ZGlhbnRlcyI7czo1OiJyb3V0ZSI7czoxNzoiZXN0dWRpYW50ZXMuaW5kZXgiO31zOjEwOiJ1c3VhcmlvX2lkIjtpOjI7czoxNDoidXN1YXJpb19ub21icmUiO3M6MzA6IlBSVUVCQV9EQVNIQk9BUkRfUHJvZmVzb3IgVGVzdCI7czoxNDoidXN1YXJpb19jb3JyZW8iO3M6Mjc6InBydWViYS5kYXNoYm9hcmRAdGVzdC5sb2NhbCI7czoxMToidXN1YXJpb19kbmkiO3M6ODoiOTk5OTkwMDEiO3M6MTQ6InVzdWFyaW9fcm9sX2lkIjtpOjY7czoxMToidXN1YXJpb19yb2wiO3M6ODoiUHJvZmVzb3IiO30=	1794925151
CfLidWsoLiX8onLkc7luEggEG1y8apaDWoug9c0f	\N	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/157.0.0.0 Safari/537.36	YTozOntzOjY6Il90b2tlbiI7czo0MDoid3JuTlc5azB4WHhac0tPU0ZsWUM0YjVrajZwUnRhdXpnWWh0U0dhVCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=	1794944379
t9Fm4NlSVnn56t9IiTd3YhrwsuNbSfEWHkJDfjhJ	\N	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/157.0.0.0 Safari/537.36 Edg/157.0.0.0	YTozOntzOjY6Il90b2tlbiI7czo0MDoicGZES3JXcExycjZGSnVPSWhBZ1RqWGV3ZG4xUlVmQjVKVnNtT3hlMiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=	1794925314
qQqP2MNnYvwObzrkPk3VI2lXxlZNGIYbqeWVf9AF	\N	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/157.0.0.0 Safari/537.36	YTozOntzOjY6Il90b2tlbiI7czo0MDoiQ3Y5aTdzNGduOWZ3emNDRDlDbXZMekxxMTNnV2dNT0VLTHZkbjhKMyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fX0=	1794880562
oZMrT1SB9oQS0gwH6jKtrY3vkMeX6n7yTNGdO9Nn	\N	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/157.0.0.0 Safari/537.36	YTozOntzOjY6Il90b2tlbiI7czo0MDoiU0tpVEFrazlEbnBjdzNHNW1BenE5RWtzQjhnTVlqSkFvVGlrWjFWeiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fX0=	1794887099
DXGQadaf9xlhHgGRX5JKoZNvyYQBpVkE21p3KiZT	\N	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/157.0.0.0 Safari/537.36	YTozOntzOjY6Il90b2tlbiI7czo0MDoicEt1QU5YWHhaTFFHVVByM3ZCQ0szOVMydEs5Mks3anVsN1lncHppQyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=	1794923375
UVr7i2F91MgAvSvUmZ3Kuze6QfjI0dLjxAfD22zb	\N	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/157.0.0.0 Safari/537.36	YTozOntzOjY6Il90b2tlbiI7czo0MDoiY2R0VVljZnowRkRxNWxDTFFBd3IwZVBaaE44MjQ5STROVmIxNkJrdiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fX0=	1794944959
Qan2M4vliiTs9aAeIpgdiqwTkfsEWtt978yzSML4	\N	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/157.0.0.0 Safari/537.36	YTo5OntzOjY6Il90b2tlbiI7czo0MDoiVmJEUWpBZ25VeTJ2VEVrQVdxWXlCc3JScGtqTVNGeWFncTNRU05VMyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzM6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9pbmNpZGVuY2lhcyI7czo1OiJyb3V0ZSI7czoxNzoiaW5jaWRlbmNpYXMuaW5kZXgiO31zOjEwOiJ1c3VhcmlvX2lkIjtpOjk7czoxNDoidXN1YXJpb19ub21icmUiO3M6NDoiam9lbCI7czoxNDoidXN1YXJpb19jb3JyZW8iO3M6MTQ6ImpvZWxAZ21haWwuY29tIjtzOjExOiJ1c3VhcmlvX2RuaSI7czo4OiIxNDcyNTgzNiI7czoxNDoidXN1YXJpb19yb2xfaWQiO2k6NztzOjExOiJ1c3VhcmlvX3JvbCI7czo5OiJEaXJlY3RvcmEiO30=	1794944974
zB2AwwXqvQHvn3NBTIkZKHy353iUF81up9GtavVd	\N	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/157.0.0.0 Safari/537.36	YTozOntzOjY6Il90b2tlbiI7czo0MDoick9TMGpDUUZ1QUhhWnNNNTZoS0wwTkcwcDRxdU5BVzBnR3dUNGNqSiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fX0=	1794923703
DAuxoRNRReGCUAQsE1HUCZuyYgIaF7P7dhaJeRzm	\N	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/157.0.0.0 Safari/537.36	YTozOntzOjY6Il90b2tlbiI7czo0MDoiSEVxdmk2Qmt5MDk4bUx5TWtTeEJPU1dzZjFMWE5LeDZpSm0yb2l1ViI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=	1795025876
OcmeNnagqnbtnpQd2Bx21OOp6OlGlb9YeQIp2IYQ	\N	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/157.0.0.0 Safari/537.36	YTozOntzOjY6Il90b2tlbiI7czo0MDoiZUVkOTVTa2pwYjZNM2pqZXN4RUtGa0ZVVHJXQjlDOFY1Zk0weXRGaiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fX0=	1794924109
yZcAJpTitkPUE81AxZvKe9HiYlUTgHTu1Y7FUyJl	\N	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/157.0.0.0 Safari/537.36	YTozOntzOjY6Il90b2tlbiI7czo0MDoiOGNTYnZsOUdrNEpnQU1Kd2w0QU5lbjF0Q2dpRkRvSzZaNEkyV1FkbiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fX0=	1794924201
Pc5xhqswT7ihRAfozM70CcyqHSh5wjvbg2RuHGaZ	\N	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/157.0.0.0 Safari/537.36	YTozOntzOjY6Il90b2tlbiI7czo0MDoiSnRlb1hHSFl0UmF4M2NITU8ydEtPbjRHT0ZQTmdGdzg1cDN5T205SSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fX0=	1794924369
I61kJWIQf75wZcS5Id50hnjC9pSMqnMqgTUhaG5O	\N	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/157.0.0.0 Safari/537.36	YTozOntzOjY6Il90b2tlbiI7czo0MDoidjlJbU1sWnIwaTREaDNWZ3BWSkZtQ0RucUQwR1MxTlh5YU1EUzhVdiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=	1794872520
mHZB9nYKWpZa22st1Z0hxpEekneDEu6gSRkBBG0E	\N	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/157.0.0.0 Safari/537.36	YTozOntzOjY6Il90b2tlbiI7czo0MDoiejh4UWJ0UWR4RXNGWDhpNEx2c284MVFpaWRSR09YaFJkbGY4Zjd4YyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fX0=	1794870157
2eN1bf0RYiLwfGHWr1SkhZROL5110RzVpAmxejqV	\N	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/157.0.0.0 Safari/537.36	YTozOntzOjY6Il90b2tlbiI7czo0MDoiYmh4RWpERm1abFdmY1NvQklBWXlBZFNiY2ZsQVlMbHh4YVJnd1hmcyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fX0=	1794876319
OiYPC6limf7y50BExA3bbU3zMO0oguMfzp4jKgSz	\N	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/157.0.0.0 Safari/537.36	YTozOntzOjY6Il90b2tlbiI7czo0MDoiZEppRUJqREg0RE1RUFRHeGEyWEFKc0RDQ3FNQ04yd3hpbHpFZGNTSyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fX0=	1794876373
mvtIVgIc043yyNDmP8tzvIQt91nfpAuk0ijwszXN	\N	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/157.0.0.0 Safari/537.36	YTozOntzOjY6Il90b2tlbiI7czo0MDoiaDYwWW04NHJKdTJ3RUdvOTRRcWNDMHM3clVld2xRNWl6cElmNncyQSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fX0=	1794870168
6Q9ejp1Je9aOiFmoL58LjKD6jT8CKsg0nhLupEdb	\N	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/157.0.0.0 Safari/537.36	YTozOntzOjY6Il90b2tlbiI7czo0MDoiaXlKUzhIZ3Q1aldpcEdlT2RpQUt2bUNuU1VZZHNBRU1tMlVpZG4zaSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fX0=	1794870182
DUhWgtirXUBsLxqhQueHQONmgyPSbmZS7JKvObeJ	\N	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/157.0.0.0 Safari/537.36	YTozOntzOjY6Il90b2tlbiI7czo0MDoieXpDb29mWVV6YWlYbU94S041cUptR3VWT2Q5SjcwVkZRZzFtY3l0bSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fX0=	1794870602
la7fWhTdiIeYQN8NWoD07g5xGqOQs9BLcx1JZZD4	\N	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/157.0.0.0 Safari/537.36	YTozOntzOjY6Il90b2tlbiI7czo0MDoib0xjRzVzcWd2dURIMDhtbFp5SWFlNVV1VXluOHBtTGpuYUlFN1p2ZSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fX0=	1794870203
rZfmdGL52DVA0ufjwzBPAQHN9OM2wk5CZ1h0Z5cC	\N	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/157.0.0.0 Safari/537.36	YTo5OntzOjY6Il90b2tlbiI7czo0MDoiMUl6QTB1TnpxQ1pQNjRmUzN3RzRwaGZ5VnJBc3VieFlkczlkQ2R5eSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fXM6MTA6InVzdWFyaW9faWQiO2k6OTtzOjE0OiJ1c3VhcmlvX25vbWJyZSI7czo0OiJqb2VsIjtzOjE0OiJ1c3VhcmlvX2NvcnJlbyI7czoxNDoiam9lbEBnbWFpbC5jb20iO3M6MTE6InVzdWFyaW9fZG5pIjtzOjg6IjE0NzI1ODM2IjtzOjE0OiJ1c3VhcmlvX3JvbF9pZCI7aTo3O3M6MTE6InVzdWFyaW9fcm9sIjtzOjk6IkRpcmVjdG9yYSI7fQ==	1794870610
F0hFqjJFKTlt0u844P4PATg0qC5q9hm6lWeSnae3	\N	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/157.0.0.0 Safari/537.36	YTo5OntzOjY6Il90b2tlbiI7czo0MDoield2WGNCN3pnOFNWSGdPekE1UDE3SU9VWFVYVVJMQlc3TTlQVnJzeCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9kYXNoYm9hcmQiO3M6NToicm91dGUiO3M6OToiZGFzaGJvYXJkIjt9czoxMDoidXN1YXJpb19pZCI7aToyO3M6MTQ6InVzdWFyaW9fbm9tYnJlIjtzOjMwOiJQUlVFQkFfREFTSEJPQVJEX1Byb2Zlc29yIFRlc3QiO3M6MTQ6InVzdWFyaW9fY29ycmVvIjtzOjI3OiJwcnVlYmEuZGFzaGJvYXJkQHRlc3QubG9jYWwiO3M6MTE6InVzdWFyaW9fZG5pIjtzOjg6Ijk5OTk5MDAxIjtzOjE0OiJ1c3VhcmlvX3JvbF9pZCI7aTo2O3M6MTE6InVzdWFyaW9fcm9sIjtzOjg6IlByb2Zlc29yIjt9	1794887747
lKsXGkKlJFufNTdsPypyq6tkGbpyq0qGC0qRAn3a	\N	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/157.0.0.0 Safari/537.36	YTozOntzOjY6Il90b2tlbiI7czo0MDoicTI1bnNLcjFCNUFCeks5Zk5oMjl5NnJvdDc5ZmxOcXVlZURLZ2VkYSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fX0=	1794923419
YdvVLvwPVpiWhIl6gaLqKNdNfe7xD3JNl6ws384W	\N	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/157.0.0.0 Safari/537.36	YToyOntzOjY6Il90b2tlbiI7czo0MDoiYkxUMFY1M3lza2Q3R2Y0eHJjRWJabUpyQjhMb2F6cVV0S3JzeEd4QSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==	1794923673
kidl36Izy2fFtiyh317nnktQyJ3Lm1zLJYPk5ety	\N	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/157.0.0.0 Safari/537.36	YTozOntzOjY6Il90b2tlbiI7czo0MDoiMFQwZUVjNDkxaVRhdUQwT1VYWGprT29YY01VQ2tWbmo5cjdreDlDbiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fX0=	1794870639
ALHfDQWGa9VGRKL90GqHlAmn4YNp2cP2JwWX6DEY	\N	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/157.0.0.0 Safari/537.36	YTozOntzOjY6Il90b2tlbiI7czo0MDoiU2hHRzlETzk0UHFSUlg3QTlIM3FIZGF2aVlwYjFPQ3YyQU5sWFFReiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fX0=	1794871053
VWU3ifO1PhKadryZh81VWyBG4msgs97UNSO5wObh	\N	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/157.0.0.0 Safari/537.36	YTozOntzOjY6Il90b2tlbiI7czo0MDoiVXoxc01WQ3gzQWx2VU5WUWlqU01qVExEclRFSGZSRVFoMmZocVJ0VSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=	1794869812
Wwo1bP1TPlS8KSl2p9uKU5aN7F80U10TLgYkbhEh	\N	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/157.0.0.0 Safari/537.36	YTo5OntzOjY6Il90b2tlbiI7czo0MDoiQ2c2Q0l0ejdNc2h0ekNqd3dSTlF2Mm5TVWZ6Y2g1bDB2UTZOb1M0cSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9kYXNoYm9hcmQiO3M6NToicm91dGUiO3M6OToiZGFzaGJvYXJkIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czoxMDoidXN1YXJpb19pZCI7aTo5O3M6MTQ6InVzdWFyaW9fbm9tYnJlIjtzOjQ6ImpvZWwiO3M6MTQ6InVzdWFyaW9fY29ycmVvIjtzOjE0OiJqb2VsQGdtYWlsLmNvbSI7czoxMToidXN1YXJpb19kbmkiO3M6ODoiMTQ3MjU4MzYiO3M6MTQ6InVzdWFyaW9fcm9sX2lkIjtpOjc7czoxMToidXN1YXJpb19yb2wiO3M6OToiRGlyZWN0b3JhIjt9	1795031249
\.


--
-- Data for Name: tipos_incidencia; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.tipos_incidencia (id, nombre) FROM stdin;
1	Agresión física
2	Agresión verbal
3	Acoso escolar (bullying)
4	Ciberacoso
5	Discriminación
6	Daños a la infraestructura
7	Hurto o robo
8	Conducta disruptiva
9	Incumplimiento de normas
10	Otros
\.


--
-- Data for Name: users; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.users (id, name, email, dni, email_verified_at, password, remember_token, created_at, updated_at, rol_id, estado_aprobacion, activo, grado_registro, curso_registro_id) FROM stdin;
2	PRUEBA_DASHBOARD_Profesor Test	prueba.dashboard@test.local	99999001	\N	$2y$12$jRAX824KUJsl9U.fZLaYw.kL1dNuCAD2dwlEQ1tuOXy0MyE1cY51.	\N	2026-11-10 15:59:05	2026-11-10 15:59:05	6	Aprobado	t	\N	\N
3	PRUEBA_DASHBOARD_ADMIN_Profesor	prueba.dashboard.admin@test.local	99999002	\N	$2y$12$wZV30IPqZsYQ05E0DjBvAOhwB4Dajdrxp1MAgBT/oympnP6W.WFIq	\N	2026-11-10 16:09:36	2026-11-10 16:09:36	6	Aprobado	t	\N	\N
4	Admin Tungasuca	admin@tungasuca.edu.pe	12345678	\N	$2y$12$.odlN9cxPzkYTKyo.m25xumafzVwLSmGHSUcZBNCDVfUqAYw4Ftym	\N	2026-11-10 20:52:30	2026-11-10 20:52:30	1	Aprobado	t	\N	\N
1	Smitt Vidaurre	vidaurresmitt@gmail.com	77676402	\N	$2y$12$15u7ABZYPDFE8tfoN74J2.dqKhTXS9.7PBY8E4CS3sehw8YhZFMOy	\N	2026-11-10 15:55:36	2026-11-10 20:56:16	6	Rechazado	t	\N	\N
5	soe	vidaurreleo3@gmail.com	87654321	\N	$2y$12$cObGHC1A3tPJoS9cR7SwwORXtT7LxtlSrNTd8Cv4YVgSuDxtf2DgW	\N	2026-11-11 10:35:21	2026-11-11 10:35:21	7	Aprobado	t	\N	\N
8	zeus	zeus@gmail.com	98765432	\N	$2y$12$0hn/xRWkU3m2VEYbHIxDceQSp/KaGR0Bx3TQRvwCfrluZkxWBdQWu	\N	2026-11-11 16:47:39	2026-11-11 16:51:38	6	Aprobado	t	PRUEBA_1ro	2
9	joel	joel@gmail.com	14725836	\N	$2y$12$THlygvx.mfbYFu57YlVorOp2NGfv1yioZ2OfhgAcldI0U9QOHDMsi	\N	2026-11-12 11:23:58	2026-11-12 11:23:58	7	Aprobado	t	\N	\N
\.


--
-- Name: asignaciones_docentes_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.asignaciones_docentes_id_seq', 4, true);


--
-- Name: chatbot_conversaciones_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.chatbot_conversaciones_id_seq', 4, true);


--
-- Name: chatbot_mensajes_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.chatbot_mensajes_id_seq', 10, true);


--
-- Name: cursos_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.cursos_id_seq', 24, true);


--
-- Name: estados_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.estados_id_seq', 15, true);


--
-- Name: estudiantes_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.estudiantes_id_seq', 9, true);


--
-- Name: failed_jobs_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.failed_jobs_id_seq', 1, false);


--
-- Name: gravedades_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.gravedades_id_seq', 17, true);


--
-- Name: incidencia_fotografias_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.incidencia_fotografias_id_seq', 1, true);


--
-- Name: incidencias_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.incidencias_id_seq', 172, true);


--
-- Name: jobs_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.jobs_id_seq', 1, false);


--
-- Name: migrations_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.migrations_id_seq', 22, true);


--
-- Name: roles_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.roles_id_seq', 12, true);


--
-- Name: secciones_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.secciones_id_seq', 9, true);


--
-- Name: tipos_incidencia_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.tipos_incidencia_id_seq', 10, true);


--
-- Name: users_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.users_id_seq', 9, true);


--
-- Name: asignaciones_docentes asignacion_docente_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.asignaciones_docentes
    ADD CONSTRAINT asignacion_docente_unique UNIQUE (usuario_id, curso_id, seccion_id);


--
-- Name: asignaciones_docentes asignaciones_docentes_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.asignaciones_docentes
    ADD CONSTRAINT asignaciones_docentes_pkey PRIMARY KEY (id);


--
-- Name: cache_locks cache_locks_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.cache_locks
    ADD CONSTRAINT cache_locks_pkey PRIMARY KEY (key);


--
-- Name: cache cache_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.cache
    ADD CONSTRAINT cache_pkey PRIMARY KEY (key);


--
-- Name: chatbot_conversaciones chatbot_conversaciones_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.chatbot_conversaciones
    ADD CONSTRAINT chatbot_conversaciones_pkey PRIMARY KEY (id);


--
-- Name: chatbot_mensajes chatbot_mensajes_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.chatbot_mensajes
    ADD CONSTRAINT chatbot_mensajes_pkey PRIMARY KEY (id);


--
-- Name: cursos cursos_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.cursos
    ADD CONSTRAINT cursos_pkey PRIMARY KEY (id);


--
-- Name: estados estados_nombre_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.estados
    ADD CONSTRAINT estados_nombre_unique UNIQUE (nombre);


--
-- Name: estados estados_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.estados
    ADD CONSTRAINT estados_pkey PRIMARY KEY (id);


--
-- Name: estudiantes estudiantes_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.estudiantes
    ADD CONSTRAINT estudiantes_pkey PRIMARY KEY (id);


--
-- Name: failed_jobs failed_jobs_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.failed_jobs
    ADD CONSTRAINT failed_jobs_pkey PRIMARY KEY (id);


--
-- Name: failed_jobs failed_jobs_uuid_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.failed_jobs
    ADD CONSTRAINT failed_jobs_uuid_unique UNIQUE (uuid);


--
-- Name: gravedades gravedades_nombre_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.gravedades
    ADD CONSTRAINT gravedades_nombre_unique UNIQUE (nombre);


--
-- Name: gravedades gravedades_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.gravedades
    ADD CONSTRAINT gravedades_pkey PRIMARY KEY (id);


--
-- Name: incidencia_fotografias incidencia_fotografias_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.incidencia_fotografias
    ADD CONSTRAINT incidencia_fotografias_pkey PRIMARY KEY (id);


--
-- Name: incidencia_tipo incidencia_tipo_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.incidencia_tipo
    ADD CONSTRAINT incidencia_tipo_unique UNIQUE (incidencia_id, tipo_incidencia_id);


--
-- Name: incidencias incidencias_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.incidencias
    ADD CONSTRAINT incidencias_pkey PRIMARY KEY (id);


--
-- Name: job_batches job_batches_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.job_batches
    ADD CONSTRAINT job_batches_pkey PRIMARY KEY (id);


--
-- Name: jobs jobs_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.jobs
    ADD CONSTRAINT jobs_pkey PRIMARY KEY (id);


--
-- Name: migrations migrations_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.migrations
    ADD CONSTRAINT migrations_pkey PRIMARY KEY (id);


--
-- Name: password_reset_tokens password_reset_tokens_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.password_reset_tokens
    ADD CONSTRAINT password_reset_tokens_pkey PRIMARY KEY (email);


--
-- Name: roles roles_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.roles
    ADD CONSTRAINT roles_pkey PRIMARY KEY (id);


--
-- Name: secciones secciones_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.secciones
    ADD CONSTRAINT secciones_pkey PRIMARY KEY (id);


--
-- Name: sessions sessions_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.sessions
    ADD CONSTRAINT sessions_pkey PRIMARY KEY (id);


--
-- Name: tipos_incidencia tipos_incidencia_nombre_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.tipos_incidencia
    ADD CONSTRAINT tipos_incidencia_nombre_unique UNIQUE (nombre);


--
-- Name: tipos_incidencia tipos_incidencia_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.tipos_incidencia
    ADD CONSTRAINT tipos_incidencia_pkey PRIMARY KEY (id);


--
-- Name: users users_dni_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_dni_unique UNIQUE (dni);


--
-- Name: users users_email_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_email_unique UNIQUE (email);


--
-- Name: users users_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_pkey PRIMARY KEY (id);


--
-- Name: cache_expiration_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX cache_expiration_index ON public.cache USING btree (expiration);


--
-- Name: cache_locks_expiration_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX cache_locks_expiration_index ON public.cache_locks USING btree (expiration);


--
-- Name: chatbot_conv_usuario_idx; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX chatbot_conv_usuario_idx ON public.chatbot_conversaciones USING btree (usuario_id);


--
-- Name: chatbot_msg_conv_fecha_id_idx; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX chatbot_msg_conv_fecha_id_idx ON public.chatbot_mensajes USING btree (conversacion_id, created_at, id);


--
-- Name: jobs_queue_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX jobs_queue_index ON public.jobs USING btree (queue);


--
-- Name: sessions_last_activity_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX sessions_last_activity_index ON public.sessions USING btree (last_activity);


--
-- Name: sessions_user_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX sessions_user_id_index ON public.sessions USING btree (user_id);


--
-- Name: asignaciones_docentes asignaciones_docentes_curso_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.asignaciones_docentes
    ADD CONSTRAINT asignaciones_docentes_curso_id_foreign FOREIGN KEY (curso_id) REFERENCES public.cursos(id);


--
-- Name: asignaciones_docentes asignaciones_docentes_seccion_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.asignaciones_docentes
    ADD CONSTRAINT asignaciones_docentes_seccion_id_foreign FOREIGN KEY (seccion_id) REFERENCES public.secciones(id);


--
-- Name: asignaciones_docentes asignaciones_docentes_usuario_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.asignaciones_docentes
    ADD CONSTRAINT asignaciones_docentes_usuario_id_foreign FOREIGN KEY (usuario_id) REFERENCES public.users(id);


--
-- Name: chatbot_conversaciones chatbot_conversaciones_usuario_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.chatbot_conversaciones
    ADD CONSTRAINT chatbot_conversaciones_usuario_id_foreign FOREIGN KEY (usuario_id) REFERENCES public.users(id) ON DELETE RESTRICT;


--
-- Name: chatbot_mensajes chatbot_mensajes_conversacion_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.chatbot_mensajes
    ADD CONSTRAINT chatbot_mensajes_conversacion_id_foreign FOREIGN KEY (conversacion_id) REFERENCES public.chatbot_conversaciones(id) ON DELETE RESTRICT;


--
-- Name: estudiantes estudiantes_seccion_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.estudiantes
    ADD CONSTRAINT estudiantes_seccion_id_foreign FOREIGN KEY (seccion_id) REFERENCES public.secciones(id);


--
-- Name: incidencia_fotografias incidencia_fotografias_eliminado_por_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.incidencia_fotografias
    ADD CONSTRAINT incidencia_fotografias_eliminado_por_foreign FOREIGN KEY (eliminado_por) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: incidencia_fotografias incidencia_fotografias_incidencia_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.incidencia_fotografias
    ADD CONSTRAINT incidencia_fotografias_incidencia_id_foreign FOREIGN KEY (incidencia_id) REFERENCES public.incidencias(id) ON DELETE CASCADE;


--
-- Name: incidencia_tipo incidencia_tipo_incidencia_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.incidencia_tipo
    ADD CONSTRAINT incidencia_tipo_incidencia_id_foreign FOREIGN KEY (incidencia_id) REFERENCES public.incidencias(id) ON DELETE CASCADE;


--
-- Name: incidencia_tipo incidencia_tipo_tipo_incidencia_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.incidencia_tipo
    ADD CONSTRAINT incidencia_tipo_tipo_incidencia_id_foreign FOREIGN KEY (tipo_incidencia_id) REFERENCES public.tipos_incidencia(id);


--
-- Name: incidencias incidencias_curso_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.incidencias
    ADD CONSTRAINT incidencias_curso_id_foreign FOREIGN KEY (curso_id) REFERENCES public.cursos(id);


--
-- Name: incidencias incidencias_eliminado_por_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.incidencias
    ADD CONSTRAINT incidencias_eliminado_por_foreign FOREIGN KEY (eliminado_por) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: incidencias incidencias_estado_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.incidencias
    ADD CONSTRAINT incidencias_estado_id_foreign FOREIGN KEY (estado_id) REFERENCES public.estados(id);


--
-- Name: incidencias incidencias_estudiante_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.incidencias
    ADD CONSTRAINT incidencias_estudiante_id_foreign FOREIGN KEY (estudiante_id) REFERENCES public.estudiantes(id);


--
-- Name: incidencias incidencias_gravedad_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.incidencias
    ADD CONSTRAINT incidencias_gravedad_id_foreign FOREIGN KEY (gravedad_id) REFERENCES public.gravedades(id);


--
-- Name: incidencias incidencias_usuario_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.incidencias
    ADD CONSTRAINT incidencias_usuario_id_foreign FOREIGN KEY (usuario_id) REFERENCES public.users(id);


--
-- Name: users users_curso_registro_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_curso_registro_id_foreign FOREIGN KEY (curso_registro_id) REFERENCES public.cursos(id);


--
-- Name: users users_rol_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_rol_id_foreign FOREIGN KEY (rol_id) REFERENCES public.roles(id);


--
-- PostgreSQL database dump complete
--

\unrestrict PCJvGCHy4S9K2eTjCGoHkFqugHkxzw9equdrcgALbJJbWIVjmUsaZc8NtJ3M6yu

