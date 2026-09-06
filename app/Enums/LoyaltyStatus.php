<?php

namespace App\Enums;

/**
 * Classifies a customer by the two win-back signals: how close they are to
 * their next reward (proximity) and how long since their last activity
 * (inactivity). The win-back segment is the intersection of "close" and
 * "inactive"; the other cases describe why a customer sits outside it.
 */
enum LoyaltyStatus: string
{
    /** Close to the next reward and inactive: the recoverable segment. */
    case WIN_BACK = 'win_back';

    /** Close to the next reward but still active, so not at risk yet. */
    case ENGAGED_CLOSE = 'engaged_close';

    /** Inactive but far from any reward, so hard to win back. */
    case FADING_FAR = 'fading_far';

    /** Has a next reward, active, and not yet close to it. */
    case HEALTHY = 'healthy';

    /** Balance already meets or exceeds every reward threshold. */
    case NO_NEXT_REWARD = 'no_next_reward';

    /** No transaction ever recorded. */
    case NEVER_ACTIVE = 'never_active';
}
