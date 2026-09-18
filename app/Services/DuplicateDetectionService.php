<?php

namespace App\Services;

use App\Models\Dataset;
use App\Models\DuplicateGroup;
use App\Models\Image;
use Illuminate\Support\Facades\DB;

class DuplicateDetectionService
{
    public function detectForDataset(Dataset $dataset): array
    {
        $hashGroups = Image::where('dataset_id', $dataset->id)
            ->where('status', '!=', 'deleted')
            ->select('hash', DB::raw('COUNT(*) as count'))
            ->groupBy('hash')
            ->having('count', '>', 1)
            ->get();

        $duplicates = 0;
        $crossLabelDuplicates = 0;

        DB::beginTransaction();
        try {
            DuplicateGroup::where('dataset_id', $dataset->id)->delete();

            foreach ($hashGroups as $group) {
                $images = Image::where('dataset_id', $dataset->id)
                    ->where('hash', $group->hash)
                    ->where('status', '!=', 'deleted')
                    ->get();

                $labels = $images->pluck('label')->unique();
                $isCrossLabel = $labels->count() > 1;

                $duplicateGroup = DuplicateGroup::create([
                    'dataset_id' => $dataset->id,
                    'hash' => $group->hash,
                    'type' => $isCrossLabel ? 'cross_label' : 'internal',
                ]);

                $duplicateGroup->images()->attach($images->pluck('id'));
                $duplicates++;

                if ($isCrossLabel) {
                    $crossLabelDuplicates++;
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        return [
            'duplicates' => $duplicates,
            'cross_label_duplicates' => $crossLabelDuplicates,
        ];
    }

    public function detectAll(): array
    {
        $datasets = Dataset::all();
        $results = [];
        foreach ($datasets as $dataset) {
            $results[$dataset->id] = $this->detectForDataset($dataset);
        }
        return $results;
    }
}
