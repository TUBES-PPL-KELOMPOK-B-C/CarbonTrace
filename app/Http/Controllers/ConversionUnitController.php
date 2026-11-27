<?php

namespace App\Http\Controllers;

use App\Models\Appliance;
use App\Models\EmissionFactor;
use App\Models\PortionUnit;
use Illuminate\Http\Request;

class ConversionUnitController extends Controller
{
    /**
     * Read: Menampilkan daftar acuan konversi porsi makanan & daya perangkat listrik.
     */
    public function index()
    {
        $portionUnits = PortionUnit::with('emissionFactor.category')
            ->orderBy('name')
            ->get();

        $appliances = Appliance::with('emissionFactor.category')
            ->orderBy('name')
            ->get();

        return view('master-emisi.conversion-units.index', compact('portionUnits', 'appliances'));
    }

    // ==================== PORTION UNITS (SATUAN PORSI) ====================

    public function createPortion()
    {
        $emissionFactors = EmissionFactor::with('category')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('master-emisi.conversion-units.create-portion', compact('emissionFactors'));
    }

    public function storePortion(Request $request)
    {
        $validated = $request->validate([
            'emission_factor_id' => 'required|exists:emission_factors,id',
            'name'               => 'required|string|max:120',
            'gram_per_portion'   => 'required|numeric|min:0.01',
        ]);

        PortionUnit::create($validated);

        return redirect()
            ->route('conversion-units.index')
            ->with('success', 'Acuan konversi porsi berhasil ditambahkan.');
    }

    public function editPortion(PortionUnit $portionUnit)
    {
        $emissionFactors = EmissionFactor::with('category')
            ->orderBy('name')
            ->get();

        return view('master-emisi.conversion-units.edit-portion', compact('portionUnit', 'emissionFactors'));
    }

    public function updatePortion(Request $request, PortionUnit $portionUnit)
    {
        $validated = $request->validate([
            'emission_factor_id' => 'required|exists:emission_factors,id',
            'name'               => 'required|string|max:120',
            'gram_per_portion'   => 'required|numeric|min:0.01',
        ]);

        $portionUnit->update($validated);

        return redirect()
            ->route('conversion-units.index')
            ->with('success', 'Acuan konversi porsi berhasil diperbarui.');
    }

    public function destroyPortion(PortionUnit $portionUnit)
    {
        $portionUnit->delete();

        return redirect()
            ->route('conversion-units.index')
            ->with('success', 'Acuan konversi porsi berhasil dihapus.');
    }

    // ==================== APPLIANCES (DAYA PERANGKAT LISTRIK) ====================

    public function createAppliance()
    {
        $emissionFactors = EmissionFactor::with('category')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('master-emisi.conversion-units.create-appliance', compact('emissionFactors'));
    }

    public function storeAppliance(Request $request)
    {
        $validated = $request->validate([
            'emission_factor_id' => 'required|exists:emission_factors,id',
            'name'               => 'required|string|max:120',
            'wattage'            => 'required|numeric|min:0.01',
        ]);

        Appliance::create($validated);

        return redirect()
            ->route('conversion-units.index')
            ->with('success', 'Acuan daya perangkat listrik berhasil ditambahkan.');
    }

    public function editAppliance(Appliance $appliance)
    {
        $emissionFactors = EmissionFactor::with('category')
            ->orderBy('name')
            ->get();

        return view('master-emisi.conversion-units.edit-appliance', compact('appliance', 'emissionFactors'));
    }

    public function updateAppliance(Request $request, Appliance $appliance)
    {
        $validated = $request->validate([
            'emission_factor_id' => 'required|exists:emission_factors,id',
            'name'               => 'required|string|max:120',
            'wattage'            => 'required|numeric|min:0.01',
        ]);

        $appliance->update($validated);

        return redirect()
            ->route('conversion-units.index')
            ->with('success', 'Acuan daya perangkat listrik berhasil diperbarui.');
    }

    public function destroyAppliance(Appliance $appliance)
    {
        $appliance->delete();

        return redirect()
            ->route('conversion-units.index')
            ->with('success', 'Acuan daya perangkat listrik berhasil dihapus.');
    }
}