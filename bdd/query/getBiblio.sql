SELECT d.doc_id, d.titre, d.url, d.type
			 , dAmz.titre dAmzTitre, dAmz.url dAmzUrl, d.note
			 , dTof.titre dTofTitre, dTof.url dTofUrl
			FROM flux_doc d
			  INNER JOIN flux_doc dAmz ON dAmz.tronc = d.doc_id AND dAmz.type = 39
			  INNER JOIN flux_doc dTof ON dTof.tronc = dAmz.doc_id 