SELECT
			 COUNT(DISTINCT t.tag_id) nbTag
			 ,COUNT(DISTINCT rd.src_id) nbDoc
			 ,  DATE_FORMAT(d.pubDate, "%d-%c-%y") temps
             , u.login
			 FROM
			 flux_tag t
			 INNER JOIN
			 flux_rapport r ON r.monade_id = 2
			 AND r.src_obj = 'rapport'
			 AND r.dst_obj = 'tag'
			 AND r.pre_obj = 'acti'
			 AND r.pre_id = 2
			 AND t.tag_id = r.dst_id
			 INNER JOIN
			 flux_rapport rd ON rd.rapport_id = r.src_id
			 INNER JOIN
			 flux_doc d ON d.doc_id = rd.src_id AND d.parent = 1
			 INNER JOIN
			 flux_uti u ON u.uti_id = rd.pre_id
			 -- WHERE
			 --    t.tag_id = 14 -- ecosystem info
			 GROUP BY u.uti_id, temps