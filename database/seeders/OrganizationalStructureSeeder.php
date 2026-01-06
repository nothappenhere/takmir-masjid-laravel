<?php

namespace Database\Seeders;

use App\Models\OrganizationalStructure;
use App\Models\Position;
use Illuminate\Database\Seeder;

class OrganizationalStructureSeeder extends Seeder
{
    public function run(): void
    {
        OrganizationalStructure::query()->delete();

        $positions = Position::all();
        $structures = [];

        // Create organizational structure based on position hierarchy
        foreach ($positions as $position) {
            $parentStructure = null;

            if ($position->parent_id) {
                $parentStructure = OrganizationalStructure::where('position_id', $position->parent_id)->first();
            }

            $structure = OrganizationalStructure::create([
                'position_id' => $position->id,
                'parent_id' => $parentStructure ? $parentStructure->id : null,
                'order' => $position->order,
                'is_division' => $position->level === 'division_head',
                'division_name' => $position->level === 'division_head' ? "Divisi " . $position->name : null,
                'division_description' => $position->level === 'division_head' ?
                    "Bertanggung jawab atas " . strtolower($position->name) : null,
            ]);

            // Set division for staff under division heads
            if ($position->level === 'staff' && $position->parent_id) {
                $divisionHeadPosition = Position::find($position->parent_id);
                if ($divisionHeadPosition && $divisionHeadPosition->level === 'division_head') {
                    $divisionStructure = OrganizationalStructure::where('position_id', $divisionHeadPosition->id)->first();
                    if ($divisionStructure) {
                        $structure->update(['parent_id' => $divisionStructure->id]);
                    }
                }
            }

            // Set division for volunteers under staff
            if ($position->level === 'volunteer' && $position->parent_id) {
                $staffPosition = Position::find($position->parent_id);
                if ($staffPosition && $staffPosition->level === 'staff') {
                    $staffStructure = OrganizationalStructure::where('position_id', $staffPosition->id)->first();
                    if ($staffStructure) {
                        $structure->update(['parent_id' => $staffStructure->id]);
                    }
                }
            }
        }

        // Rebuild nested set structure
        $this->rebuildTree();

        $this->command->info('Organizational structure seeded successfully!');
        $this->command->info('Total structures: ' . OrganizationalStructure::count());
        $this->command->info('Divisions: ' . OrganizationalStructure::where('is_division', true)->count());
    }

    /**
     * Rebuild the nested set tree
     */
    private function rebuildTree($parentId = null, $left = 1)
    {
        $children = OrganizationalStructure::where('parent_id', $parentId)
            ->orderBy('order')
            ->get();

        $right = $left + 1;

        foreach ($children as $child) {
            $right = $this->rebuildTree($child->id, $right);

            $child->lft = $left;
            $child->rgt = $right;
            $child->depth = $parentId ? OrganizationalStructure::find($parentId)->depth + 1 : 0;
            $child->save();

            $left = $right + 1;
            $right = $left + 1;
        }

        if ($parentId) {
            $parent = OrganizationalStructure::find($parentId);
            $parent->rgt = $right;
            $parent->save();
        }

        return $right;
    }
}
