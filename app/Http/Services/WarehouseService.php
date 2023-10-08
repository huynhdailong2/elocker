<?php

namespace App\Http\Services;

use App\Models\Cluster;
use App\Consts;
use App\Utils;
use App\Traits\CustomQueryBuilder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class WarehouseService extends BaseService
{
    use CustomQueryBuilder;

    public function getClusters($params)
    {
        return Cluster::when(!empty($params['search_key']), function ($query) use ($params) {
                $searchKey = Utils::escapeLike($params['search_key']);
                $query->where(function ($subQuery) use ($searchKey) {
                    $subQuery->where('name', 'LIKE', "%{$searchKey}%");
                });
            })
            ->when(
                !empty($params['sort']) && !empty($params['sort_type']),
                function ($query) use ($params) {
                    return $query->orderBy($params['sort'], $params['sort_type']);
                },
                function ($query) {
                    return $query->orderBy('updated_at', 'desc');
                }
            )
            ->select('clusters.*')
            ->when(
                !empty($params['no_pagination']),
                function ($query) {
                    return $query->get();
                },
                function ($query) use ($params) {
                    return $query->paginate(Arr::get($params, 'limit', Consts::DEFAULT_PER_PAGE));
                }
            );
    }

    public function getClusterInfo($clusterId)
    {
        return Cluster::find($clusterId);
    }

    public function createCluster($params)
    {
        $cluster = new Cluster;
        $cluster = $this->saveData($cluster, $params);

        return $cluster;
    }

    public function updateCluster($params)
    {
        $cluster = Cluster::find($params['id']);
        $cluster = $this->saveData($cluster, $params);

        return $cluster;
    }

    public function updateVirtualCluster($id, $isVirtual)
    {
        $cluster = Cluster::query()
            ->where('id', $id)
            ->first();

        if($cluster) {
            $cluster
                ->fill(
                    [
                        'is_virtual' => $isVirtual
                    ]
                )
                ->save();
        }

        return $cluster;
    }

    public function deleteCluster($clusterId)
    {
        $cluster = Cluster::find($clusterId);
        $cluster->delete();

        return $cluster;
    }
}
