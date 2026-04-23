<?php

namespace App\Enums;

enum IncidentKind: string
{
    case VisibilityHidden = 'visibility_hidden';
    case WindowBlur = 'window_blur';
    case NavigationAttempt = 'navigation_attempt';
}
