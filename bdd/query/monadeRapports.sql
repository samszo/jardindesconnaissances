SELECT m.titre mTitre,
       r.rapport_id,
       d.doc_id,
       d.titre dTitre,
       d.url,
       t.tag_id,
       t.code,
       e.exi_id,
       e.nom
		, svgETD.svg_id svgETDid
		, svgETD.data svgETData
		, svgD.svg_id svgDid
		, svgD.data svgData
		, svgE.svg_id svgDid
		, svgE.data svgData
		, svgT.svg_id svgDid
		, svgT.data svgData
  FROM flux_monade m
       INNER JOIN flux_rapport r ON r.monade_id = m.monade_id
       INNER JOIN flux_exitagdoc etd ON etd.exitagdoc_id = r.exitagdoc_id
	INNER JOIN flux_svg svgETD ON etd.exitagdoc_id = svgETD.obj_id AND svgETD.obj_type = 'Model_DbTable_Flux_ExiTagDoc'
       INNER JOIN flux_doc d ON d.doc_id = etd.doc_id
	INNER JOIN flux_svg svgD ON d.doc_id = svgD.obj_id AND svgD.obj_type = 'Model_DbTable_Flux_Doc'
       INNER JOIN flux_tag t ON t.tag_id = etd.tag_id
	INNER JOIN flux_svg svgT ON t.tag_id = svgT.obj_id AND svgT.obj_type = 'Model_DbTable_Flux_Tag'
       INNER JOIN flux_exi e ON e.exi_id = etd.exi_id
	INNER JOIN flux_svg svgE ON e.exi_id = svgE.obj_id AND svgE.obj_type = 'Model_DbTable_Flux_Exi'
 WHERE m.monade_id = 3
-- GROUP BY m.monade_id