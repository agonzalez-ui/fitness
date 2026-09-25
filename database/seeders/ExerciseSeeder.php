<?php

namespace Database\Seeders;

use App\Enums\Equipment as E;
use App\Enums\MuscleGroup as M;
use App\Models\Exercise;
use Illuminate\Database\Seeder;

class ExerciseSeeder extends Seeder
{
    public function run(): void
    {
        // [nombre, grupo, equipo, series, reps, descanso, instrucciones]
        $exercises = [
            // Pecho
            ['Press de banca con barra', M::Chest, E::Barbell, 4, '8-10', 90, 'Baja la barra a la mitad del pecho con los codos a ~45°. Empuja sin despegar los glúteos del banco.'],
            ['Press inclinado con mancuernas', M::Chest, E::Dumbbell, 3, '10-12', 75, 'Banco a 30°. Baja las mancuernas hasta la altura del pecho y sube juntándolas arriba.'],
            ['Press de banca con mancuernas', M::Chest, E::Dumbbell, 3, '10-12', 75, 'Escápulas juntas, baja controlado y empuja en línea recta.'],
            ['Aperturas con mancuernas', M::Chest, E::Dumbbell, 3, '12-15', 60, 'Codos ligeramente flexionados; abre hasta sentir estiramiento y cierra como abrazando un árbol.'],
            ['Cruce de poleas', M::Chest, E::Cable, 3, '12-15', 60, 'Un paso adelante, cruza las manos frente al pecho apretando al final.'],
            ['Flexiones de pecho', M::Chest, E::Bodyweight, 3, '10-15', 60, 'Cuerpo en línea recta, baja el pecho casi al suelo y empuja.'],
            ['Press de pecho en máquina', M::Chest, E::Machine, 3, '10-12', 60, 'Ajusta el asiento para que las manijas queden a la altura del pecho.'],
            ['Fondos en paralelas', M::Chest, E::Bodyweight, 3, '8-12', 90, 'Inclina el torso hacia adelante para enfocar pecho. Baja hasta 90° de codo.'],
            // Espalda
            ['Dominadas', M::Back, E::Bodyweight, 4, '6-10', 90, 'Agarre prono un poco más ancho que los hombros. Sube hasta pasar la barbilla.'],
            ['Jalón al pecho', M::Back, E::Cable, 4, '10-12', 75, 'Lleva la barra a la parte alta del pecho sacando pecho y juntando escápulas.'],
            ['Remo con barra', M::Back, E::Barbell, 4, '8-10', 90, 'Torso a ~45°, espalda neutra. Lleva la barra al ombligo.'],
            ['Remo con mancuerna a una mano', M::Back, E::Dumbbell, 3, '10-12', 60, 'Apoya rodilla y mano en el banco. Lleva el codo hacia la cadera.'],
            ['Remo sentado en polea', M::Back, E::Cable, 3, '10-12', 60, 'Espalda recta, tira hacia el abdomen y aprieta las escápulas.'],
            ['Peso muerto convencional', M::Back, E::Barbell, 4, '5-8', 120, 'Barra pegada a las piernas, espalda neutra, empuja el suelo con los pies.'],
            ['Pullover en polea', M::Back, E::Cable, 3, '12-15', 60, 'Brazos casi rectos, baja la barra hasta los muslos contrayendo dorsales.'],
            ['Hiperextensiones', M::Back, E::Bodyweight, 3, '12-15', 60, 'Baja controlado y sube hasta alinear el torso con las piernas, sin hiperextender.'],
            // Hombros
            ['Press militar con barra', M::Shoulders, E::Barbell, 4, '8-10', 90, 'De pie, abdomen firme. Empuja la barra por encima de la cabeza.'],
            ['Press de hombro con mancuernas', M::Shoulders, E::Dumbbell, 3, '10-12', 75, 'Sentado con respaldo. Sube las mancuernas sin chocarlas.'],
            ['Elevaciones laterales', M::Shoulders, E::Dumbbell, 3, '12-15', 45, 'Codos ligeramente flexionados, sube hasta la altura de los hombros.'],
            ['Elevaciones frontales', M::Shoulders, E::Dumbbell, 3, '12-15', 45, 'Alterna brazos, sube hasta la altura de los ojos sin balancear.'],
            ['Pájaros (deltoide posterior)', M::Shoulders, E::Dumbbell, 3, '12-15', 45, 'Torso inclinado, abre los brazos hacia los lados apretando la espalda alta.'],
            ['Face pull', M::Shoulders, E::Cable, 3, '12-15', 45, 'Cuerda a la altura de la cara, tira separando las manos hacia las orejas.'],
            ['Encogimientos con mancuernas', M::Shoulders, E::Dumbbell, 3, '12-15', 45, 'Sube los hombros hacia las orejas y sostén un segundo.'],
            // Bíceps
            ['Curl con barra', M::Biceps, E::Barbell, 3, '10-12', 60, 'Codos pegados al cuerpo, sube sin balancear el torso.'],
            ['Curl alterno con mancuernas', M::Biceps, E::Dumbbell, 3, '10-12', 60, 'Gira la muñeca al subir (supinación).'],
            ['Curl martillo', M::Biceps, E::Dumbbell, 3, '10-12', 60, 'Agarre neutro, palmas mirándose.'],
            ['Curl en polea', M::Biceps, E::Cable, 3, '12-15', 45, 'Tensión constante; baja lento.'],
            ['Curl concentrado', M::Biceps, E::Dumbbell, 3, '10-12', 45, 'Codo apoyado en el muslo, sube lento y aprieta.'],
            // Tríceps
            ['Extensión de tríceps en polea', M::Triceps, E::Cable, 3, '12-15', 45, 'Codos fijos a los costados; extiende completamente.'],
            ['Press francés', M::Triceps, E::Barbell, 3, '10-12', 60, 'Acostado, baja la barra hacia la frente moviendo solo los codos.'],
            ['Extensión sobre la cabeza con mancuerna', M::Triceps, E::Dumbbell, 3, '10-12', 60, 'Sostén una mancuerna con ambas manos y baja detrás de la cabeza.'],
            ['Fondos en banco', M::Triceps, E::Bodyweight, 3, '10-15', 45, 'Manos en el banco detrás de ti, baja hasta 90°.'],
            ['Patada de tríceps', M::Triceps, E::Dumbbell, 3, '12-15', 45, 'Torso inclinado, codo alto y extiende hacia atrás.'],
            // Piernas
            ['Sentadilla con barra', M::Legs, E::Barbell, 4, '8-10', 120, 'Pies al ancho de hombros, baja hasta que el muslo quede paralelo. Rodillas en dirección de los pies.'],
            ['Sentadilla goblet', M::Legs, E::Dumbbell, 3, '10-12', 75, 'Sostén una mancuerna frente al pecho; baja con el torso erguido.'],
            ['Prensa de piernas', M::Legs, E::Machine, 4, '10-12', 90, 'No bloquees las rodillas arriba; baja hasta 90°.'],
            ['Zancadas con mancuernas', M::Legs, E::Dumbbell, 3, '10-12', 60, 'Paso largo, la rodilla de atrás casi toca el suelo. Reps por pierna.'],
            ['Sentadilla búlgara', M::Legs, E::Dumbbell, 3, '8-10', 75, 'Pie trasero sobre el banco. Baja vertical. Reps por pierna.'],
            ['Extensión de cuádriceps', M::Legs, E::Machine, 3, '12-15', 60, 'Extiende completamente y baja controlado.'],
            ['Curl femoral acostado', M::Legs, E::Machine, 3, '12-15', 60, 'Lleva los talones hacia los glúteos sin levantar la cadera.'],
            ['Peso muerto rumano', M::Legs, E::Barbell, 4, '8-10', 90, 'Rodillas semiflexionadas, lleva la cadera atrás hasta sentir los isquiotibiales.'],
            ['Elevación de talones de pie', M::Legs, E::Machine, 4, '15-20', 45, 'Sube en puntas y baja estirando el gemelo.'],
            ['Sentadilla libre', M::Legs, E::Bodyweight, 3, '15-20', 45, 'Sin peso, brazos al frente, baja lo más que puedas con buena postura.'],
            // Glúteos
            ['Hip thrust con barra', M::Glutes, E::Barbell, 4, '10-12', 90, 'Espalda alta en el banco, empuja la cadera hacia arriba y aprieta glúteos.'],
            ['Puente de glúteos', M::Glutes, E::Bodyweight, 3, '15-20', 45, 'Acostado, sube la cadera y sostén 2 segundos arriba.'],
            ['Patada de glúteo en polea', M::Glutes, E::Cable, 3, '12-15', 45, 'Extiende la pierna hacia atrás sin arquear la espalda. Reps por pierna.'],
            ['Abducción de cadera en máquina', M::Glutes, E::Machine, 3, '15-20', 45, 'Abre las piernas controlado y regresa lento.'],
            ['Caminata lateral con banda', M::Glutes, E::Band, 3, '15', 45, 'Banda sobre las rodillas, semisentadilla, pasos laterales. Pasos por lado.'],
            ['Peso muerto sumo', M::Glutes, E::Kettlebell, 3, '10-12', 75, 'Pies abiertos, puntas afuera, sube apretando glúteos.'],
            // Abdomen
            ['Plancha', M::Core, E::Bodyweight, 3, '30-45 s', 45, 'Antebrazos en el suelo, cuerpo recto, aprieta abdomen y glúteos.'],
            ['Plancha lateral', M::Core, E::Bodyweight, 3, '20-30 s', 30, 'Cadera alta, cuerpo en línea. Tiempo por lado.'],
            ['Crunch abdominal', M::Core, E::Bodyweight, 3, '15-20', 30, 'Despega los hombros del suelo sin jalar el cuello.'],
            ['Elevación de piernas', M::Core, E::Bodyweight, 3, '12-15', 45, 'Acostado, sube las piernas rectas sin despegar la zona lumbar.'],
            ['Russian twist', M::Core, E::Bodyweight, 3, '20', 30, 'Sentado con pies elevados, gira el torso de lado a lado.'],
            ['Mountain climbers', M::Core, E::Bodyweight, 3, '30 s', 30, 'En posición de plancha, lleva rodillas al pecho alternando rápido.'],
            ['Rueda abdominal', M::Core, E::Bodyweight, 3, '8-12', 60, 'De rodillas, rueda hacia adelante sin arquear la espalda.'],
            ['Crunch en polea', M::Core, E::Cable, 3, '12-15', 45, 'De rodillas, flexiona el torso llevando los codos a los muslos.'],
            // Cardio
            ['Caminadora', M::Cardio, E::Machine, 1, '20 min', 0, 'Ritmo moderado; puedes inclinar para más intensidad.'],
            ['Bicicleta estática', M::Cardio, E::Machine, 1, '20 min', 0, 'Mantén cadencia constante.'],
            ['Elíptica', M::Cardio, E::Machine, 1, '20 min', 0, 'Empuja y jala con brazos y piernas.'],
            ['Saltar la cuerda', M::Cardio, E::Bodyweight, 5, '1 min', 30, 'Saltos pequeños sobre la punta de los pies.'],
            ['Jumping jacks', M::Cardio, E::Bodyweight, 3, '45 s', 30, 'Abre piernas y brazos a la vez, ritmo constante.'],
            ['Burpees', M::Cardio, E::Bodyweight, 3, '10-12', 60, 'Sentadilla, plancha, flexión, salto. Sin pausas.'],
            ['Remo en máquina', M::Cardio, E::Machine, 1, '10 min', 0, 'Empuja con piernas primero, luego jala con brazos.'],
            // Cuerpo completo
            ['Kettlebell swing', M::FullBody, E::Kettlebell, 4, '15', 60, 'Bisagra de cadera; la fuerza sale de los glúteos, no de los brazos.'],
            ['Thruster con mancuernas', M::FullBody, E::Dumbbell, 3, '10-12', 75, 'Sentadilla frontal que termina en press sobre la cabeza.'],
            ['Clean con barra', M::FullBody, E::Barbell, 4, '5', 120, 'Explosivo desde el suelo hasta los hombros. Requiere técnica.'],
            ['Paseo del granjero', M::FullBody, E::Dumbbell, 3, '40 m', 60, 'Camina con mancuernas pesadas, hombros atrás y abdomen firme.'],
        ];

        foreach ($exercises as [$name, $group, $equipment, $sets, $reps, $rest, $instructions]) {
            Exercise::updateOrCreate(['name' => $name], [
                'muscle_group' => $group,
                'equipment' => $equipment,
                'default_sets' => $sets,
                'default_reps' => $reps,
                'default_rest_seconds' => $rest,
                'instructions' => $instructions,
            ]);
        }
    }
}
