<?php

namespace Onlyoung4u\AsApi\Model;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AsActionLog extends BaseModel
{
    /**
     * @param \DateTimeInterface $date
     * @return string
     */
    protected function serializeDate(\DateTimeInterface $date) {
        return $date->format($this->dateFormat ?: 'Y-m-d H:i:s');
    }

    /**
     * 操作人
     *
     * @return BelongsTo
     */
    public function operator(): BelongsTo
    {
        return $this->belongsTo(AsUser::class, 'action_uid');
    }
}