<?php

namespace AcMarche\Theme\Lib\Pivot\Enums;

enum UrnEnum: string
{
    case DATE_OBJECT = "urn:obj:date";
    case DATE_DEB_VALID = 'urn:fld:datedebvalid';
    case DATE_FIN_VALID = "urn:fld:datefinvalid";
    case DATE_DEB = 'urn:fld:date:datedeb';
    case DATE_END = "urn:fld:date:datefin";
    case DATE_DETAIL_OUVERTURE = 'urn:fld:date:detailouv';//nl:urn:fld:date:detailouv
    case DATE_OUVERTURE_HEURE_1 = 'urn:fld:date:houv1';
    case DATE_FERMETURE_HEURE_1 = 'urn:fld:date:hferm1';
    case DATE_OUVERTURE_HEURE_2 = 'urn:fld:date:houv2';
    case DATE_FERMETURE_HEURE_2 = 'urn:fld:date:hferm2';
    case DATE_RANGE = 'urn:fld:date:daterange';
    case MEDIAS_PARTIAL = "urn:lnk:media";
    case MEDIAS_AUTRE = "urn:lnk:media:autre";
    case MEDIA_DEFAULT = "urn:lnk:media:defaut";
    case URL = "urn:fld:url";
    case WEB = "urn:fld:urlweb";
    case FACEBOOK = "urn:fld:urlfacebook";


}
