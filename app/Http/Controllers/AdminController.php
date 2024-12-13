<?php

namespace App\Http\Controllers;

use App\Models\Admin; // Assurez-vous que le modèle Admin existe
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AdminController extends Controller
{
    // Récupérer tous les administrateurs
    public function index()
    {
        $admins = Admin::OrderBy('id', 'desc')->paginate(10);
        return response()->json($admins);
    }

    // Créer un nouvel administrateur
    public function store(Request $request)
    {
        try {
            // Validation des données
            $request->validate([
                'nom' => 'required|string|max:255',
                'email' => 'required|email|unique:admins|regex:/^[\w\.-]+@[\w\.-]+\.[a-zA-Z]{2,6}$/',
                'password' => 'required|string|min:8',
            ]);

            // Création d'un nouvel administrateur
            $admin = new Admin();
            $admin->nom = $request['nom'];
            $admin->email = $request['email'];
            $admin->password = bcrypt($request['password']); // Hachage du mot de passe
            $admin->save();

            return response()->json($admin, 201); // 201 Created
        } catch (ModelNotFoundException $e) {
            // Gestion des erreurs de validation
            return response()->json([
                'message' => 'Les données fournies sont invalides.',
                'errors' => $e->errors(),
            ], 422); // 422 Unprocessable Entity
        } catch (\Exception $e) {
            // Gestion des autres exceptions
            return response()->json([
                'message' => 'Une erreur est survenue.',
                'error' => $e->getMessage(),
            ], 500); // 500 Internal Server Error
        }
    }

    // Récupérer un administrateur spécifique
    public function show($id)
    {
        $admin = Admin::find($id);
        if (!$admin) {
            return response()->json(['message' => 'Administrateur non trouvé'], 404);
        }
        return response()->json($admin);
    }

    // Mettre à jour un administrateur existant
    public function update(Request $request, $id)
    {
        $admin = Admin::find($id);
        if (!$admin) {
            return response()->json(['message' => 'Administrateur non trouvé'], 404);
        }

        // Validation des données
        $validator = Validator::make($request->all(), [
            'nom' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:admins,email,' . $admin->id,
            'password' => 'sometimes|required|string|min:8',
            // Autres validations spécifiques aux admins
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Mise à jour des attributs
        if ($request->has('password')) {
            $admin->password = bcrypt($request['password']); // Hachage du mot de passe
        }
        $admin->update($request->except('password')); // Met à jour les autres attributs
        return response()->json($admin);
    }

    // Supprimer un administrateur
    public function destroy($id)
    {
        $admin = Admin::find($id);
        if (!$admin) {
            return response()->json(['message' => 'Administrateur non trouvé'], 404);
        }

        $admin->delete();
        return response()->json(['message' => 'Administrateur supprimé avec succès', 204]);
    }
}
