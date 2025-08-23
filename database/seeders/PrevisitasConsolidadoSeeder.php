<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PrevisitaConsolidado;
use Carbon\Carbon;

class PrevisitasConsolidadoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Verificar si ya existen datos para evitar duplicación
        if (PrevisitaConsolidado::count() > 0) {
            $this->command->info('Las previsitas ya existen en la base de datos. Saltando seeder...');
            return;
        }

        $this->command->info('Insertando previsitas consolidadas...');

        // Obtener el primer usuario disponible
        $defaultUserId = \App\Models\User::first()->id ?? 1;
        $this->command->info('Usando usuario ID: ' . $defaultUserId);

        $previsitas = [
            [
                'lugar' => 'Finca el Pantano',
                'fecha_visita' => '2024-02-15',
                'vencimiento' => '2025-02-14',
                'responsable' => 'Carlos Mario',
                'aprobacion_sitio' => true,
                'observaciones_recomendaciones' => 'El establecimiento No cumple con ciertos requisitos como seguros a terceros, al no ser un sitio turistico. Es una finca doméstica.',
                'user_id' => $defaultUserId
            ],
            [
                'lugar' => 'Monserrate',
                'fecha_visita' => '2024-04-29',
                'vencimiento' => '2025-04-28',
                'responsable' => 'Gustavo Bolaños',
                'aprobacion_sitio' => true,
                'observaciones_recomendaciones' => 'Entorno seguro con vigilancia policial',
                'user_id' => $defaultUserId
            ],
            [
                'lugar' => 'Rio Claro',
                'fecha_visita' => '2023-11-02',
                'vencimiento' => '2025-11-01',
                'responsable' => 'Gustavo Bolaños',
                'aprobacion_sitio' => true,
                'observaciones_recomendaciones' => 'No se recomienda para estudiantes con casos especiales en Psicología por factor riesgo del entorno.',
                'user_id' => $defaultUserId
            ],
            [
                'lugar' => 'Santander',
                'fecha_visita' => '2024-01-10',
                'vencimiento' => '2026-01-09',
                'responsable' => 'Gustavo Bolaños',
                'aprobacion_sitio' => true,
                'observaciones_recomendaciones' => 'Existe riesgo de infección al tomar agua. Al inicio de la cueva se debe tener precaución ya que el piso es resbaloso. En la parte final se tiene un salto de 5m al agua.',
                'user_id' => $defaultUserId
            ],
            [
                'lugar' => 'Suesca',
                'fecha_visita' => '2023-11-09',
                'vencimiento' => '2025-11-09',
                'responsable' => 'Ramiro Aycardi',
                'aprobacion_sitio' => true,
                'observaciones_recomendaciones' => 'Cuenta con todas las medidas de seguridad',
                'user_id' => $defaultUserId
            ],
            [
                'lugar' => 'Valle escondido y Cueva de la fabrica',
                'fecha_visita' => '2023-11-09',
                'vencimiento' => '2025-11-08',
                'responsable' => 'Ramiro Aycardi',
                'aprobacion_sitio' => true,
                'observaciones_recomendaciones' => 'Es un sitio amplio y seguro. cuenta con todas las indicaciones de seguridad',
                'user_id' => $defaultUserId
            ],
            [
                'lugar' => 'Rio Claro',
                'fecha_visita' => '2023-11-02',
                'vencimiento' => '2025-11-01',
                'responsable' => 'Gustavo Bolaños',
                'aprobacion_sitio' => true,
                'observaciones_recomendaciones' => 'Los estudiantes con proceso en psicología deben abstenerse de asistir. Validar recomendaciones en el informe.',
                'user_id' => $defaultUserId
            ],
            [
                'lugar' => 'Parque Laguna Cacique Guatavita',
                'fecha_visita' => '2023-10-25',
                'vencimiento' => '2025-10-24',
                'responsable' => 'Carlos Mario - Walter Díaz',
                'aprobacion_sitio' => true,
                'observaciones_recomendaciones' => 'Llevar calzado de agarre e hidratación. Seguir indicaciones del Guía. No cuenta con registro Nacional de Turismo',
                'user_id' => $defaultUserId
            ],
            [
                'lugar' => 'Bio Parque la Reserva',
                'fecha_visita' => '2023-09-29',
                'vencimiento' => '2024-09-28',
                'responsable' => 'Carlos Mario',
                'aprobacion_sitio' => true,
                'observaciones_recomendaciones' => 'Espacios seguros. Llevar ropa y elementos adecuados para la zona.',
                'user_id' => $defaultUserId
            ],
            [
                'lugar' => 'Desierto de la Tatacoa',
                'fecha_visita' => '2023-12-04',
                'vencimiento' => '2025-12-03',
                'responsable' => 'Miguel Bernal',
                'aprobacion_sitio' => true,
                'observaciones_recomendaciones' => 'Utilizar ropa cómoda y acorde al clima. Espacio Seguro. Trocha está bien acondicionada. Baños en óptimo estado/separados por Género. Algunos senderos pueden ser resbalosos.',
                'user_id' => $defaultUserId
            ],
            [
                'lugar' => 'Hacienda Pozo Claro',
                'fecha_visita' => '2024-08-27',
                'vencimiento' => '2026-08-26',
                'responsable' => 'Yomara Cristancho',
                'aprobacion_sitio' => true,
                'observaciones_recomendaciones' => 'La hacienda es un sitio Seguro. Se recomienda el uso de Bota de agarre o de lluvia. Es importante el acompañamiento del área de enfermería.',
                'user_id' => $defaultUserId
            ],
            [
                'lugar' => 'Casa del Lago',
                'fecha_visita' => '2024-08-27',
                'vencimiento' => '2026-08-26',
                'responsable' => 'Yomara Cristancho',
                'aprobacion_sitio' => true,
                'observaciones_recomendaciones' => 'La hacienda es un sitio Seguro. Se recomienda el uso de Bota de agarre o de lluvia. Es importante el acompañamiento del área de enfermería.',
                'user_id' => $defaultUserId
            ],
            [
                'lugar' => 'HUMEDAL LA CONEJERA',
                'fecha_visita' => '2024-09-02',
                'vencimiento' => '2026-09-01',
                'responsable' => 'MARIA DEL PILAR SILVA',
                'aprobacion_sitio' => true,
                'observaciones_recomendaciones' => 'El Parque Ecológico Distrital Humedal la Conejera es un espacio seguro que no representa riesgos significativos para la comunidad Victoria. Por otra parte, es un ecosistema cuidado pertinentemente por las entidades distritales que ofrece a sus visitantes un recorrido con señalizaciones pertinentes',
                'user_id' => $defaultUserId
            ],
            [
                'lugar' => 'FUNDACION FUNTRAVIDI',
                'fecha_visita' => '2024-08-07',
                'vencimiento' => '2025-08-06',
                'responsable' => 'MARIA DEL PILAR SILVA',
                'aprobacion_sitio' => true,
                'observaciones_recomendaciones' => 'La Fundación Funtravidi presenta condiciones seguras para los estudiantes. Sin embargo, es de suma importancia seguir las recomendaciones de seguridad descritas con anterioridad.',
                'user_id' => $defaultUserId
            ],
            [
                'lugar' => 'CENTRAL DE ABASTOS DEL NORTE CODABAS',
                'fecha_visita' => '2024-09-09',
                'vencimiento' => '2025-09-08',
                'responsable' => 'Yomara Cristancho',
                'aprobacion_sitio' => true,
                'observaciones_recomendaciones' => 'CODABAS, es una organización comercial y empresarial que alquila bodegas para venta de alimentos (plaza de mercado) cuenta con espacios seguros (parqueaderos, cuenta con plan de emergencias vigente. Esta empresa está bajo la ley 675 agosto de 2001 Copropiedades',
                'user_id' => $defaultUserId
            ],
            [
                'lugar' => 'ASOCIACION CULTURAL HILOS MAGICOS',
                'fecha_visita' => '2024-09-10',
                'vencimiento' => '2025-09-08',
                'responsable' => 'Yomara Cristancho',
                'aprobacion_sitio' => true,
                'observaciones_recomendaciones' => 'Hilos Mágicos es una Asociación Cultural sin ánimo de lucro, orientada a la investigación, creación, producción, promoción y difusión del arte de los títeres en particular y la cultura en general, con énfasis en el público infantil y familiar. Cuenta con espacios seguros para los estudiantes. Sin restricción para la visita. No tiene parqueadero, pero se puede coordinar con facilidad el descenso de los estudiantes.',
                'user_id' => $defaultUserId
            ],
            [
                'lugar' => 'HELLO PARK - CENTRO COMERCIAL NUESTRO BOGOTA',
                'fecha_visita' => '2024-09-16',
                'vencimiento' => '2025-09-15',
                'responsable' => 'Yomara Cristancho',
                'aprobacion_sitio' => true,
                'observaciones_recomendaciones' => 'Hello Park es un área de juegos para niños. El parque combina juegos multimedia, atracciones interactivas y proyecciones coloridas, todo envuelto en diferentes historias. Es un espacio creado para niños entre 1 a 10 años. El parque cuenta con todas las medidas de seguridad, se encuentra en el piso 3 del centro comercial Nuestro Bogotá - recomendación asistir con enfermera - tiene un cobro de parqueadero de $79 el minuto - tarifa única $7.000, cuenta con póliza médica de EMERMEDICA para sus invitados. Se aconseja llevar medias antideslizantes pues toda la actividad es sin zapatos. NO tiene restricciones para acceso a la visita.',
                'user_id' => $defaultUserId
            ],
            [
                'lugar' => 'MUSEO NACIONAL DE COLOMBIA',
                'fecha_visita' => '2024-10-02',
                'vencimiento' => null, // Según lineamientos del protocolo visitas, no requiere visita
                'responsable' => 'Yomara Cristancho',
                'aprobacion_sitio' => true,
                'observaciones_recomendaciones' => 'Esta entidad no requiere visita de inspección pues es una institución sin ánimo de lucro, permanente y al servicio de la sociedad, que investiga, colecciona, conserva, interpreta y exhibe el patrimonio material e inmaterial de colombia, Es un espacio seguro con todos los lineamientos de seguridad. Se recomienda ingreso de estudiantes por la Calle 28, allí descienden los estudiantes y toman el camino para ingresar por la portería principal (sobre la Cra 7º) la salida debe ser por la calle 29 al lado del Juan Valdez.',
                'user_id' => $defaultUserId
            ],
            [
                'lugar' => 'Plaza de Bolivar - Las Aguas',
                'fecha_visita' => '2024-09-26',
                'vencimiento' => '2025-09-26',
                'responsable' => 'Wilson Rueda',
                'aprobacion_sitio' => true,
                'observaciones_recomendaciones' => 'Riesgo bajo, los estudiantes previa aprobación de las visitas, vía whatsapp y email, además con recorrido previo de los docentes de la previsita, asistirán a recorridos guiados en donde recogerán información para sus trabajos de este par de proyectos empresariales que se desarrollan en el sector. En CityU, se espera a las 2:00 pm del miércoles 30 de octubre realizar la visita que ya cuenta con el aval de Angelica Cabra (Gerente de estrategia y comunicaciones de CITYU). Observaciones. Se cuenta en ambos lugares con recorrido guiado y acompañamiento de docentes de las materias.',
                'user_id' => $defaultUserId
            ],
            [
                'lugar' => 'Parque de los Niños y Niñas Parque Simon Bolivar',
                'fecha_visita' => '2024-10-08',
                'vencimiento' => '2025-10-08',
                'responsable' => 'Yomara Cristancho',
                'aprobacion_sitio' => true,
                'observaciones_recomendaciones' => 'El parque metropolitano Simón Bolívar es el escenario deportivo y recreativo más importante de Bogotá por su gran extensión y su estratégica ubicación en el corazón de la ciudad, está administrado por el IDRD Instituto Distrital para la Recreación y el Deporte. No requiere visita de inspección. Se recomienda hacer reserva 15 días antes de la visita y deben llevar enfermera y acompañamiento de 1 adulto por cada 5 niños.',
                'user_id' => $defaultUserId
            ],
            [
                'lugar' => 'Centro comercial Santafé - Cine Colombia',
                'fecha_visita' => '2024-11-15',
                'vencimiento' => '2025-11-15',
                'responsable' => 'Wilson Rueda',
                'aprobacion_sitio' => true,
                'observaciones_recomendaciones' => 'El Centro comercial ofrece espacios y mecanismos seguros para su visita',
                'user_id' => $defaultUserId
            ],
            [
                'lugar' => 'HOTEL ARCOBALENO - San Francisco (Cundinamarca)',
                'fecha_visita' => '2024-12-09',
                'vencimiento' => null, // NO APROBADA
                'responsable' => 'Yomara Cristancho',
                'aprobacion_sitio' => false,
                'observaciones_recomendaciones' => 'Según visita realizada no se recomienda el sitio para llevar a los estudiantes del TVS, pues no da garantías de seguridad y salubridad. El contacto no contesta, por lo tanto no se pudo verificar la documentación.',
                'user_id' => $defaultUserId
            ],
            [
                'lugar' => 'MUSEO DEL ORO BOGOTA',
                'fecha_visita' => '2025-12-11',
                'vencimiento' => null, // Según lineamientos del protocolo visitas, no requiere visita
                'responsable' => 'Yomara Cristancho',
                'aprobacion_sitio' => true,
                'observaciones_recomendaciones' => 'Esta entidad no requiere visita de inspección pues es una institución sin ánimo de lucro, permanente y al servicio de la sociedad, que investiga, colecciona, conserva, interpreta y exhibe el patrimonio material e inmaterial de colombia, Es un espacio seguro con todos los lineamientos de seguridad.',
                'user_id' => $defaultUserId
            ],
            [
                'lugar' => 'FINCA LA AMAPOLA - GAMA CUNDINAMARCA',
                'fecha_visita' => '2024-12-18',
                'vencimiento' => '2025-12-18',
                'responsable' => 'Carlos Mario López',
                'aprobacion_sitio' => true,
                'observaciones_recomendaciones' => 'La finca cuenta con la documentación completa, los guías encargados Eiwanne tienen capacitación de primeros auxilios y cuentan con la experiencia para manejo de grupo. La planta física es una finca, los estudiantes pasarán 1 noche en camping la zona de camping es segura, cuenta con baños y duchas aseadas, los caminos están delimitados, se recomienda uso de calzado apropiado en caso de lluvias, El área donde tomarán la alimentación es buena. Los estudiantes pasarán una noche en las instalaciones.',
                'user_id' => $defaultUserId
            ],
            [
                'lugar' => 'Talleres Colmillo, Diantres y Galeria Binario',
                'fecha_visita' => '2025-01-29',
                'vencimiento' => '2026-09-29',
                'responsable' => 'Wilson Rueda',
                'aprobacion_sitio' => true,
                'observaciones_recomendaciones' => 'Mayoría de aspectos dado que son dos viviendas adaptadas para el servicio de talleristas no tienen a la mano los documentos referidos, Posibilidad de caminata, unos diez minutos. Salvo que deben cruzarse dos semáforos para contemplar y minimizar riesgos., Wilson refiere que la salida es apta para estudiantes de Bachillerato de 7º en adelante',
                'user_id' => $defaultUserId
            ],
            [
                'lugar' => 'PLANETARIO DE BOGOTA',
                'fecha_visita' => '2025-01-14',
                'vencimiento' => '2026-01-14',
                'responsable' => 'Yomara Cristancho',
                'aprobacion_sitio' => true,
                'observaciones_recomendaciones' => 'El Planetario de Bogotá es un escenario cultural y de divulgación científica integrado desde 2012 al Instituto Distrital de las Artes - Idartes, entidad adscrita a la Secretaría de Cultura, Recreación y Deporte de la Alcaldía Mayor de Bogotá. No requiere Visita de Inspección',
                'user_id' => $defaultUserId
            ],
            [
                'lugar' => 'FINCA EL PLACER Y COLEGIO LOS ALPES - LA CALERA',
                'fecha_visita' => '2024-12-18',
                'vencimiento' => '2025-01-31',
                'responsable' => 'Yomara Cristancho',
                'aprobacion_sitio' => true,
                'observaciones_recomendaciones' => 'Finca El Placer: Es una finca lechera ubicada en la vereda Aurora Baja, La Calera, a unos 40 minutos del colegio Victoria. Cuenta con espacios amplios, una pequeña montaña sin riesgo de acceso para los estudiantes, y una quebrada pequeña cercada que no representa un riesgo. El equipo ambiental del TVS llevará a cabo la siembra de árboles en el lugar. Se recomienda la presencia de la enfermera del TVS para acompañar a los estudiantes. Visita al Colegio Los Alpes: Se realizó una inspección a 3 km de la finca El Placer, pero no se permitió el ingreso a las instalaciones debido a remodelaciones para el regreso de los estudiantes (calendario A). El administrador permitió tomar fotos fuera del colegio y se pudo observar la zona en la que los niños podrán sembrar los árboles. El colegio es seguro y cuenta con espacios apropiados.',
                'user_id' => $defaultUserId
            ],
            [
                'lugar' => 'CONGRESO DE LA REPÚBLICA',
                'fecha_visita' => '2025-02-20',
                'vencimiento' => null, // Según lineamientos del protocolo visitas, no requiere visita
                'responsable' => 'Yomara Cristancho',
                'aprobacion_sitio' => true,
                'observaciones_recomendaciones' => 'El Congreso de la República de Colombia es la legislatura bicameral de la República de Colombia integrado por el Senado y por la Cámara de Representantes, Es una entidad sin ánimo de lucro, cuenta con toda la reglamentación de seguridad. Es importante diligenciar formato de ingreso (compartido a las docentes y asistente de sección) Toda persona que ingrese al recinto debe llevar documento de identidad original. Contar con 20 minutos antes de la cita pues el registro de ingreso es demorado.',
                'user_id' => $defaultUserId
            ],
            [
                'lugar' => 'FUNDACIÓN TIERRA DE NANDO',
                'fecha_visita' => '2025-02-22',
                'vencimiento' => '2026-02-22',
                'responsable' => 'Maria del Pilar Silva',
                'aprobacion_sitio' => true,
                'observaciones_recomendaciones' => 'Finca, las instalaciones se encuentran en excelente estado, en las zonas verdes donde registran actividades de competencia están aptas, Cuentan con espacio para ingreso de ruta de 35 pasajeros. Los senderos no son de riesgo para el tránsito de estudiantes. Las instalaciones cuentan con la estructuración adecuada la cual mitiga el riesgo',
                'user_id' => $defaultUserId
            ],
            [
                'lugar' => 'LA GUAJIRA',
                'fecha_visita' => '2023-03-03',
                'vencimiento' => null,
                'responsable' => 'Maria del Pilar Silva',
                'aprobacion_sitio' => true,
                'observaciones_recomendaciones' => 'La Guajira y su recorrido por su parte baja y media, presenta condiciones seguras para los estudiantes. Sin embargo, es de suma importancia seguir las recomendaciones de seguridad descritas con anterioridad.',
                'user_id' => $defaultUserId
            ],
            [
                'lugar' => 'ESCUELA DE INGENIERIA',
                'fecha_visita' => '2025-08-25',
                'vencimiento' => null, // Según lineamientos del protocolo visitas, no requiere visita
                'responsable' => 'Grupo de cinco docentes TVS logran alistamiento de convenio.',
                'aprobacion_sitio' => true,
                'observaciones_recomendaciones' => 'El grupo de docentes que realizan la visita (Domenica (Math.), Julian (Ciencias), Cristina (Math.), Andrea (Coordinadora PAI), Hugo (Diseño) y Ciencias (Pili CAS) dan cuenta de las enormes posibilidades que ofrece la Escuela de Ingeniería, que pone al servicio del TVS su infraestructura y varios proyectos como posibilidad e visita y desarrollo de experiencias significativas, que además, dada la ubicación y cercanía con nuestro colegio, abrió el convenio que hoy se atrasa entre las dos instituciones. Se plantea como fecha de visita el 21 de marzo de 2025 denominada preliminarmente TV STEM - Humanity Pk - 10º.',
                'user_id' => $defaultUserId
            ],
            [
                'lugar' => 'CENTRO PARA LA EDUCACION AMBIENTAL BISVITA',
                'fecha_visita' => '2025-04-11',
                'vencimiento' => null,
                'responsable' => 'Yomara Cristancho',
                'aprobacion_sitio' => true,
                'observaciones_recomendaciones' => 'Es una finca, su propósito es explicar a la comunidad y ejemplificar el concepto de basura cero (en inglés, "zero waste"). ubicada en el KM 28 Vía Bogotá-La Vega a 1hr y 30min del Colegio. Cuenta con espacios amplios, con espacios de reciclaje procesamiento de la materia orgánica y sobre el aprovechamiento de sus productos, los espacios que recorrerán los estudiantes son cercados y no representa un riesgo. Se recomienda la presencia de la enfermera del TVS para acompañar a los estudiantes. Llevar zapatos de agarre y/o botas pantaneras. Baños: se utiliza un sistema de inodoros secos tipo letrina los cuales forman parte de su programa.',
                'user_id' => $defaultUserId
            ],
            [
                'lugar' => 'PASTELERIA JACQUES BOGOTA',
                'fecha_visita' => '2025-05-06',
                'vencimiento' => null,
                'responsable' => 'Yomara Cristancho',
                'aprobacion_sitio' => true,
                'observaciones_recomendaciones' => 'La pastelería Jacques es un local comercial ubicado en la calle 109 con 15 pastelería francesa con buenas instalaciones, la visita se solicita para tres (3) estudiantes y dos (2) docentes, tomarán un café con postre. El lugar es apto para la visita sin embargo para grupo mayor a 10 estudiantes se sugiere evaluar pues es pequeña y en caso de emergencia la salida es muy pequeña. Reportan concepto sanitario favorable Pendiente plan de emergencias',
                'user_id' => $defaultUserId
            ],
            [
                'lugar' => 'MUNICIPIO VILLAPINZÓN - PARAMO DE GUACHENEQUE',
                'fecha_visita' => '2025-04-21',
                'vencimiento' => null,
                'responsable' => 'Wilson Rueda',
                'aprobacion_sitio' => true,
                'observaciones_recomendaciones' => 'Se recomienda presencia de enfermera del TVS para acompañar a los estudiantes. Se recomienda llevar ropa de cambio, zapatos botas pantaneras, además de una maleta pequeña viajera para acomodar botellita, bloqueador, snack pequeño para caminata, cachucha paraguas pequeño o impermeable. Los estudiantes deben estar preparados para una caminata de aproximadamente hora y media en el páramo y dado que la salida se realiza en mayo 22 existe probabilidad de lluvia alternada con momentos soleados por ello debe estar preparados para ello. Los estudiantes de cafetería contarán con snack y almuerzo viajero.',
                'user_id' => $defaultUserId
            ]
        ];

        foreach ($previsitas as $previsita) {
            // Asegurar que cada previsita tenga un user_id válido
            $previsita['user_id'] = $defaultUserId;
            PrevisitaConsolidado::create($previsita);
        }

        $this->command->info('Se insertaron ' . count($previsitas) . ' previsitas consolidadas exitosamente.');
    }
}
