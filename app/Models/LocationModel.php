<?php

namespace App\Models;

/**
 * Modèle gérant la table 'location'
 */
class LocationModel extends BaseModel
{
    protected $table = 'location';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;

    protected $allowedFields = [
        'latitude',
        'longitude',
        'address',
        'note',
        'city_id',
        'is_favorite'
    ];

    protected $validationRules = [
        'latitude'  => 'required|decimal',
        'longitude' => 'required|decimal',
        'address'   => 'required|max_length[255]',
        'note'      => 'permit_empty|max_length[1000]',
        'city_id'   => 'required|integer',
    ];

    /**
     * Définit un lieu comme favori unique : réinitialise l'ancien favori
     * puis marque le lieu fourni comme favori.
     *
     * @param int $locationId Identifiant du lieu à marquer comme favori
     * @return bool           true si la mise à jour a réussi
     */
    public function setFavorite(int $locationId): bool
    {
        $this->where('is_favorite', 1)->set('is_favorite', 0)->update();
        return $this->update($locationId, ['is_favorite' => 1]);
    }

    /**
     * Récupère le lieu favori courant, enrichi du nom et du code postal de la ville.
     *
     * @return array|null Lieu favori, ou null si aucun favori défini
     */
    public function getFavorite(): ?array
    {
        return $this->select('location.*, city.name as city_name, city.zipcode as city_zipcode')
            ->join('city', 'city.id = location.city_id')
            ->where('location.is_favorite', 1)
            ->first();
    }

    /**
     * Retire le statut favori de tous les lieux.
     *
     * @return bool true si la mise à jour a réussi
     */
    public function clearFavorite(): bool
    {
        return $this->where('is_favorite', 1)->set('is_favorite', 0)->update();
    }
}