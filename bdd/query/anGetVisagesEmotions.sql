SELECT 
    dv.doc_id,
    MIN(d.doc_id) pId,
    MIN(d.titre) label,
    MIN(d.url) original,
    MIN(dp.titre) theme,
    MIN(DATE_FORMAT(rDeb.valeur, '%Y-%m-%d')) temps,
    MIN(DATEDIFF(DATE_FORMAT(rDeb.valeur, '%Y-%m-%d'),
            FROM_UNIXTIME(0)) * 24 * 3600) MinDate,
    dv.titre,
    /*
    om.source imgFull,
    om.item_id idOmk,
    */
    COUNT(v.visage_id) nbVisage,
    SUM(FIND_IN_SET(v.joy,
            'VERY_UNLIKELY,UNLIKELY,POSSIBLE,LIKELY,VERY_LIKELY')) / COUNT(v.visage_id) joie,
    SUM(FIND_IN_SET(v.anger,
            'VERY_UNLIKELY,UNLIKELY,POSSIBLE,LIKELY,VERY_LIKELY')) / COUNT(v.visage_id) colere,
    SUM(FIND_IN_SET(v.surprise,
            'VERY_UNLIKELY,UNLIKELY,POSSIBLE,LIKELY,VERY_LIKELY')) / COUNT(v.visage_id) surprise,
    SUM(FIND_IN_SET(v.sorrow,
            'VERY_UNLIKELY,UNLIKELY,POSSIBLE,LIKELY,VERY_LIKELY')) / COUNT(v.visage_id) ennui,
    SUM(FIND_IN_SET(v.blurred,
            'VERY_UNLIKELY,UNLIKELY,POSSIBLE,LIKELY,VERY_LIKELY')) / COUNT(v.visage_id) flou,
    SUM(FIND_IN_SET(v.headwear,
            'VERY_UNLIKELY,UNLIKELY,POSSIBLE,LIKELY,VERY_LIKELY')) / COUNT(v.visage_id) chapeau
FROM
    flux_doc d
        INNER JOIN
    flux_rapport rDeb ON rDeb.src_id = d.parent
        AND rDeb.src_obj = 'doc'
        AND rDeb.dst_obj = 'tag'
        AND rDeb.dst_id = 4
        INNER JOIN
    flux_doc dp ON dp.doc_id = d.parent
        INNER JOIN
    flux_doc dv ON dv.parent = d.doc_id
        INNER JOIN
    flux_visage v ON v.doc_id = dv.doc_id
	/*
        INNER JOIN
    omk_valarnum1.value ov ON ov.value LIKE 'flux_valarnum-flux_doc-doc_id-%'
        AND SUBSTRING(ov.value, 31) = dv.doc_id
        INNER JOIN
    omk_valarnum1.media om ON om.item_id = ov.resource_id
    */
WHERE
    d.type = 1
GROUP BY dv.doc_id -- , om.item_id, om.source
-- order by idOmk desc
