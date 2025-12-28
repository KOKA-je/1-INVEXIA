<?php

namespace App\Http\Controllers;

use App\Services\LogService;
use Illuminate\Http\Request;
use App\Models\CategorieEquipement;
use Illuminate\Support\Facades\Log;

class CategorieEquipementController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = CategorieEquipement::query();

            // Filtre sur libellé de la catégorie
            if ($request->filled('libelle')) {
                $query->where('lib_cat', 'like', '%' . $request->libelle . '%');
            }

            // Filtre sur la date de création
            if ($request->filled('created_at')) {
                $query->whereDate('created_at', $request->created_at);
            }

            // Filtre sur la date de mise à jour
            if ($request->filled('updated_at')) {
                $query->whereDate('updated_at', $request->updated_at);
            }

            $categories = $query->paginate(5)->appends($request->query());

            return view('pages.categories.index', compact('categories'));
        } catch (\Throwable $e) {
            Log::error("Erreur lors du chargement des catégories d'équipements : " . $e->getMessage());
            return redirect()->back()->with('error', "Impossible de charger les catégories d'équipements.");
        }
    }

    public function create()
    {
        return view('pages.categories.create');
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'lib_cat' => 'required|string|max:255|unique:categorie_equipements',
            ]);
            CategorieEquipement::create($validated);
            LogService::addLog('Création catégorie équipement', 'Libellé: ' . $request->lib_cat);

            return redirect()->route('categories.index')->with('success', 'Catégorie d\'équipement créée avec succès.');
        } catch (\Illuminate\Validation\ValidationException $e) {

            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput()
                ->with('error', 'Erreur de validation : veuillez vérifier les données saisies.');
        } catch (\Exception $e) {

            Log::error("Erreur création catégorie équipement : " . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Erreur lors de la création : ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        try {
            $categorie = CategorieEquipement::findOrFail($id);
            return view('pages.categories.show', compact('categorie'));
        } catch (\Exception $e) {
            Log::error("Erreur affichage catégorie équipement ID {$id} : " . $e->getMessage());
            return redirect()->route('categories.index')->with('error', 'Impossible de trouver cette catégorie.');
        }
    }

    public function edit($id)
    {
        try {
            $categorie = CategorieEquipement::findOrFail($id);
            return view('pages.categories.edit', compact('categorie'));
        } catch (\Exception $e) {
            Log::error("Erreur édition catégorie équipement ID {$id} : " . $e->getMessage());
            return redirect()->route('categories.index')->with('error', 'Impossible de trouver cette catégorie.');
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $categorie = CategorieEquipement::findOrFail($id);

            $validated = $request->validate([
                'lib_cat' => 'required|string|max:255|unique:categorie_equipements,lib_cat,' . $categorie->id,
            ]);

            $categorie->update($validated);

            LogService::addLog('MAJ catégorie équipement', 'ID: ' . $id . ', Libellé: ' . $request->lib_cat);

            return redirect()->route('categories.index')->with('success', 'Catégorie d\'équipement mise à jour avec succès.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput()
                ->with('error', 'Erreur de validation : veuillez vérifier les données saisies.');
        } catch (\Exception $e) {
            Log::error("Erreur mise à jour catégorie équipement ID {$id} : " . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Erreur lors de la mise à jour : ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $categorie = CategorieEquipement::findOrFail($id);
            $categorie->delete();

            LogService::addLog('Suppression catégorie équipement', 'ID: ' . $id);

            return redirect()->route('categories.index')->with('success', 'Catégorie d\'équipement supprimée avec succès');
        } catch (\Exception $e) {
            Log::error("Erreur suppression catégorie équipement ID {$id} : " . $e->getMessage());
            return redirect()->back()->with('error', 'Erreur lors de la suppression : ' . $e->getMessage());
        }
    }
}