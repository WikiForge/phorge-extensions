<?php

final class MediaWikiWikiForgeUserPageCustomField extends MediaWikiUserPageCustomField {

	protected function getCentralAuthUrl() {
		return 'https://meta.wikiforge.net/wiki/Special:CentralAuth/';
	}

	protected function getFieldKey() {
		return 'wikiforge:externalaccount';
	}

	public function getFieldName() {
		return pht( 'WikiForge User' );
	}
}
