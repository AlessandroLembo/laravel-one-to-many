<?php

namespace Database\Seeders;

use App\Models\Project;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $projects = config('projects');

        foreach ($projects as $project) {
            $new_project = new Project();

            // alla proprietà dell'oggetto creato assegno il valore della chiave dell'array associativo

            // $project->name = $project['name'];
            // $project->description = $project['description'];
            // $project->image = $project['image'];
            // $project->slug = $project['slug'];
            // $project->project_for = $project['project_for'];
            // $project->web_platform = $project['web_platform'];
            // $project->duration_project = $project['duration_project'];

            $new_project->fill($project);
            $new_project->save();
        }
    }
}
