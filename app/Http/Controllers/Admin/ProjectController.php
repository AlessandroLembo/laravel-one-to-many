<?php

namespace App\Http\Controllers\Admin;

use App\Models\Project;
use App\Models\Type;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $projects = Project::orderby('id')->get();

        return view('admin.projects.index', compact('projects'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $project = new Project();
        $types = Type::orderBy('label')->get();
        return view('admin.projects.create', compact('project', 'types'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // validazione
        $request->validate([
            'name' => 'required|string|unique:projects',
            'description' => 'nullable|string',
            'image' => 'nullable|image', // validazione che controlla che sia un'immagine, posso anche speficare le estensioni
            'project_for' => 'string',
            'web_platform' => 'nullable|string',
            'duration_project' => 'nullable|string',
        ], [
            'name.required' => 'Il nome del progetto è obbligatorio',
            'name.unique' => "Esiste già un progetto con il nome $request->name",
            'image.image' => 'Il file caricato deve essere di tipo immagine',

        ]);

        $data = $request->all();

        $new_project = new Project();

        // controllo se mi arriva un file immagine nell'array data (potrei usare anche una funzione datami dagli helper di laravel)
        if (array_key_exists('image', $data)) {
            /* se sono qui c'è l'immagine e salvo l'url nello Storage con una Facades:
               primo argomento cartella dove voglio salvare,
               secondo argomento cosa voglio salvare.
            */
            $image_url = Storage::put('projects', $data['image']);

            // Abbiamo l'url e lo assegno alla chiave image dell'array
            $data['image'] = $image_url;
        }

        // Non possiamo fare fill() diretto perchè il database accetta solo stringhe,
        // quindi devo prima salvare il file nello storage e ottenere la stringa (link allo storage) da poter salvare nel database
        $new_project->fill($data);

        $new_project->save();

        return to_route('admin.projects.show', $new_project->id)->with('type', 'success')->with('Creazione progetto andata a buon fine');
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project)
    {
        return view('admin.projects.show', compact('project'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Project $project)
    {
        $types = Type::orderBy('label')->get();

        return view('admin.projects.edit', compact('project', 'types'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Project $project)
    {
        $request->validate([
            // Il metodo ignore sui campi unique non ostacola l'aggiornamento dello stesso progetto
            'name' => ['required', 'string', Rule::unique('projects')->ignore($project->id)],
            'description' => 'nullable|string',
            'image' => 'nullable|image', // validazione che controlla che sia un'immagine, posso anche speficare le estensioni
            'project_for' => 'string',
            'web_platform' => 'nullable|string',
            'duration_project' => 'nullable|string',
        ], [
            'name.required' => 'Il nome del progetto è obbligatorio',
            'name.unique' => "Esiste già un progetto con il nome $request->name",
            'image.image' => 'Il file caricato deve essere di tipo immagine',

        ]);

        $data = $request->all();

        if (array_key_exists('image', $data)) {
            // Se c'è già un'immagine la cancello per lasciar spazio alla nuova che voglio caricare
            if ($project->image) Storage::delete($project->image);

            /* se sono qui c'è l'immagine e salvo l'url nello Storage con una Facades:
               primo argomento cartella dove voglio salvare,
               secondo argomento cosa voglio salvare.
            */
            $image_url = Storage::put('projects', $data['image']);

            // Abbiamo l'url e lo assegno alla chiave image dell'array
            $data['image'] = $image_url;
        }

        // update fa i metodi fill() e save() insieme
        // $project->update($data);
        $project->fill($data);
        $project->save();

        return to_route('admin.projects.show', $project->id)->with('type', 'success')->with('message', "Modifiche al progetto '$project->name' apportate con successo");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project)
    {
        // Controlliamo se c'è un'immagine nel progetto da eliminare e la cancello
        if ($project->image) Storage::delete($project->image);

        // prendo il progetto e lo elimino
        $project->delete();

        // Faccio il redirect alla pagina index e stampo il messaggio di conferma eliinazione
        return to_route('admin.projects.index')->with('type', 'danger')->with('message', "Il progetto '$project->name' è stato cancellato con successo");
    }
}
