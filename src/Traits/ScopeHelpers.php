<?php

declare(strict_types=1);

namespace Nucleus\Models\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use JetBrains\PhpStorm\Pure;
use Nucleus\Models\Custom\HasCustomRelations;
use Nucleus\Models\Entity\ServiceModel;
use Service\Repository\Contracts\BaseRepositoryContract;
use Service\Role\Traits\HasFranchise;
use Service\Role\Traits\HasModules;
use Staudenmeir\EloquentHasManyDeep\HasRelationships;
use Staudenmeir\EloquentJsonRelations\HasJsonRelationships;
use Znck\Eloquent\Traits\BelongsToThrough;

/**
 * Class ScopeHelpers
 * @package ServiceEntity\Models
 */
trait ScopeHelpers
{
    use Touchy;
    use BelongsToThrough;
    use HasJsonRelationships;
    use HasCustomRelations;
    use HasRelationships;
    use HasModules;
    use HasFactory;
    use HasFranchise;

    /**
     * @var string
     */
    protected string $repository;
    /**
     * @var string
     */
    protected string $map = '';

    /**
     * @var IndexConfigurator
     */
    protected IndexConfigurator $indexConfigurator;

    /**
     * @var array
     */
    protected array $searchRules = [];

    /**
     * @return string
     */
    public static function map(): string
    {
        return (new static())->map;
    }

    /**
     * @return BaseRepositoryContract|string
     */
    #[Pure] public static function repository(): BaseRepositoryContract|string
    {
        return (new static())->repository ?? '';
    }

    /**
     * @param $status
     * @return Builder|Model|object|ServiceModel|null
     */
    public static function getStatusId($status)
    {
        return (new static())->newModelQuery()
            ->where('status', '=', $status)
            ->first([(new static())->getKeyName(), 'status'])->{(new static())->getKeyName()};
    }

    /**
     * @param $type
     * @param string $attribute
     * @return int|string
     */
    public static function getTypeId($type, string $attribute = 'type'): int|string
    {
        return (new static())->newModelQuery()
            ->where($attribute, '=', $type)
            ->first([(new static())->getKeyName(), $attribute])
            ->{(new static())->getKeyName()};
    }

    /////////////////////////////////////////////////////STATUS, TYPE, CLASS///////////////////////////////////////////

    /**
     * @param $class
     * @return int|string
     */
    public static function getClassId($class): int|string
    {
        return (new static())->newModelQuery()
            ->where('type', '=', $class)
            ->first([(new static())->getKeyName(), 'class'])->{(new static())->getKeyName()};
    }

    /**
     * @return mixed
     */
    public static function getTableName(): string
    {
        return (new static())->getTable();
    }

    /**
     * @return mixed
     */
    #[Pure] public static function getPrimaryName(): string
    {
        return (new static())->getKeyName();
    }


    ///////////////////////////////////////////////////////////HELPERS/////////////////////////////////////////////////

    /**
     * @return mixed
     */
    #[Pure] public static function getFillables(): array
    {
        return (new static())->getFillable();
    }

    /**
     * @return string
     */
    #[Pure] public function getMap(): string
    {
        return (new static())->map;
    }

    /**
     * @return BaseRepositoryContract|string
     */
    #[Pure] public function getRepository(): BaseRepositoryContract|string
    {
        return (new static())->repository ?? '';
    }

    /**
     * @param $query
     * @param array $values
     * @return array|void
     */
    public function scopeExcept($query, array $values = [])
    {
        $attributes = static::first();

        if (!$attributes) {
            return;
        }

        $attributes = $attributes->getAttributes();
        $diff_data = array_diff(array_keys($attributes), array_values($values));

        return $query->select($diff_data);
    }

    /**
     * @param Model $model
     * @return Collection
     */
    public function mergeAttributes(Model $model): Collection
    {
        return collect(array_merge($this->getAttributes(), $model->getAttributes()));
    }

    /**
     * @param Builder $query
     * @param $latitude
     * @param $longitude
     * @param int $distance
     * @return Builder
     */
    public function scopeDistanceCord(Builder $query, $latitude, $longitude, int $distance = 1): Builder
    {
        $angle_radius = $distance / 111;

        $min_lat = $latitude - $angle_radius;
        $max_lat = $latitude + $angle_radius;
        $min_lon = $longitude - $angle_radius;
        $max_lon = $longitude + $angle_radius;

        $lat_column = $this->getLatitudeColumn();
        $lut_column = $this->getLongitudeColumn();

        return $query->whereRaw(
            "$lat_column BETWEEN " . $min_lat . ' AND ' . $max_lat . " AND $lut_column BETWEEN " . $min_lon . ' AND ' . $max_lon . '    '
        );
    }

    /**
     * @return string
     */
    public function getLatitudeColumn(): string
    {
        return \defined('static::LATITUDE') ? static::LATITUDE : 'latitude';
    }

    /**
     * @return string
     */
    public function getLongitudeColumn(): string
    {
        return \defined('static::LONGITUDE') ? static::LONGITUDE : 'longitude';
    }

    /**
     * @param Builder $query
     * @param $latitude
     * @param $longitude
     * @param $inner_radius
     * @param $outer_radius
     * @return Builder
     */
    public function scopeGeofence(Builder $query, $latitude, $longitude, $inner_radius, $outer_radius): Builder
    {
        $query = $this->scopeCordDistance($query, $latitude, $longitude);

        return $query->havingRaw('distance BETWEEN ? AND ?', [$inner_radius, $outer_radius]);
    }

    /**
     * @param Builder $query
     * @param $latitude
     * @param $longitude
     * @return Builder
     */
    public function scopeCordDistance(Builder $query, $latitude, $longitude): Builder
    {
        $latName = $this->getLatitudeColumn();
        $lonName = $this->getLongitudeColumn();

        if (null === $query->getQuery()->columns) {
            $query->select($this->getTable() . '.*');
        } else {
            $query->select($query->getQuery()->columns);
        }

        $kilometers = property_exists(static::class, 'kilometers') && static::$kilometers;

        if ($kilometers) {
            $sql =
                'ROUND(((ACOS(SIN(? * PI() / 180) * SIN(' . $latName . ' * PI() / 180) + COS(? * PI() / 180) *
                COS(' . $latName . ' * PI() / 180) * COS((? - ' . $lonName . ') * PI() / 180)) * 180 / PI()) * 60 * ?), 1) as distance';

            $query->selectRaw($sql, [$latitude, $latitude, $longitude, 1.1515 * 1.609344]);
        } else {
            $sql =
                'ROUND(((ACOS(SIN(? * PI() / 180) * SIN(' . $latName . ' * PI() / 180) + COS(? * PI() / 180) *
                COS(' . $latName . ' * PI() / 180) * COS((? - ' . $lonName . ') * PI() / 180)) * 180 / PI()) * 60 * ?), 2) * 1000 as distance';

            $query->selectRaw($sql, [$latitude, $latitude, $longitude, 1.1515]);
        }

        return $query;
    }

    /**
     * @param Builder $query
     * @param $latitude
     * @param $longitude
     * @param $distance
     * @return Builder
     */
    public function scopeDistanceCordsACOS(Builder $query, $latitude, $longitude, $distance): Builder
    {
        $sql = "(6371 * ACOS(COS(radians($latitude))
                      * COS(radians(lat))
                      * COS(radians(lut) - radians($longitude))
                      + SIN(radians($latitude))
                      * SIN(radians(lat)))
                ) AS distance HAVING distance < $distance";

        $query->selectRaw($sql, [$latitude, $longitude, $latitude, $distance]);

        return $query;
    }
}
