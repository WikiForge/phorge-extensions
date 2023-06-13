<?php

final class MediaWikiWikiTideUserPageCustomField extends MediaWikiUserPageCustomField {

	protected function getCentralAuthUrl() {
		return 'https://meta.wikitide.com/wiki/Special:CentralAuth/';
	}

	protected function getFieldKey() {
		return 'wikitide:externalaccount';
	}

	public function getFieldName() {
		return pht( 'WikiTide User' );
	}
}
