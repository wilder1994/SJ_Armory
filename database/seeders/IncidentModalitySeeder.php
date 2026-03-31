<?php

namespace Database\Seeders;

use App\Models\IncidentModality;
use App\Models\IncidentType;
use Illuminate\Database\Seeder;

class IncidentModalitySeeder extends Seeder
{
    public function run(): void
    {
        $catalog = [
            'hurtada' => [
                'descuido_negligencia' => 'Hurto por descuido o negligencia del portador',
                'intimidacion' => 'Hurto mediante intimidacion (atraco directo)',
                'servicios_instalaciones' => 'Hurto en servicios domiciliarios o instalaciones',
                'transporte_armas' => 'Hurto en transporte de armas',
                'complicidad_personal' => 'Hurto interno (complicidad del personal)',
                'armerillos_cuartos' => 'Hurto en armerillos o cuartos de armas',
                'engano_suplantacion' => 'Hurto por engano o suplantacion',
                'alteracion_orden_publico' => 'Hurto en eventos de alteracion del orden publico',
                'incapacitacion_portador' => 'Hurto posterior a incapacitacion del portador',
                'relevo_turno' => 'Hurto en relevo o cambio de turno',
            ],
            'perdida' => [
                'descuido_portador' => 'Perdida por descuido del portador',
                'extravio_transporte' => 'Perdida por extravio en transporte',
                'caida_accidental' => 'Perdida por caida accidental',
                'relevo_turno' => 'Perdida durante relevo o cambio de turno',
                'mala_custodia_instalaciones' => 'Perdida por mala custodia en instalaciones',
                'desplazamientos_operativos' => 'Perdida en desplazamientos operativos',
                'situaciones_emergencia' => 'Perdida en situaciones de emergencia',
                'error_inventarios_registros' => 'Perdida por error en inventarios o registros',
                'almacenamiento_inadecuado' => 'Perdida por almacenamiento inadecuado',
                'mantenimiento_limpieza' => 'Perdida durante actividades de mantenimiento o limpieza',
            ],
            'incautada' => [
                'flagrancia' => 'Incautacion en flagrancia',
                'orden_judicial' => 'Incautacion por orden judicial',
                'operativos_control' => 'Incautacion en operativos de control (puestos de control)',
                'allanamientos_registros' => 'Incautacion en allanamientos y registros',
                'porte_ilegal' => 'Incautacion por porte ilegal de armas',
                'vencimiento_documentos' => 'Incautacion por vencimiento de permisos o documentos',
                'requisas_preventivas' => 'Incautacion en requisas preventivas',
                'capturas_detenciones' => 'Incautacion en capturas o detenciones',
                'procedimientos_administrativos' => 'Incautacion en procedimientos administrativos',
                'operativos_especiales' => 'Incautacion en operativos especiales (redadas / planes candado)',
            ],
        ];

        foreach ($catalog as $typeCode => $modalities) {
            $type = IncidentType::query()->where('code', $typeCode)->first();

            if (!$type) {
                continue;
            }

            $sortOrder = 10;

            foreach ($modalities as $code => $name) {
                IncidentModality::query()->updateOrCreate(
                    [
                        'incident_type_id' => $type->id,
                        'code' => $code,
                    ],
                    [
                        'name' => $name,
                        'sort_order' => $sortOrder,
                        'is_active' => true,
                    ]
                );

                $sortOrder += 10;
            }
        }
    }
}
