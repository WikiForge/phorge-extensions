<?php

// Source: https://raw.githubusercontent.com/wikimedia/phabricator-extensions/wmf/stable/src/customfields/MediaWikiUserpageCustomField.php

final class MediaWikiUserpageCustomField extends PhabricatorUserCustomField {
	protected $externalAccount;

	public function shouldUseStorage() {
		return false;
	}

	public function getFieldKey() {
		return 'mediawiki:externalaccount';
	}

	public function getFieldName() {
		$account = $this->getExternalAccount();
		if ( $account !== null ) {
			$uri = $account->getAccountURI();
			if ( strpos( $uri, 'wikitide.org' ) !== false ) {
				return pht( 'WikiTide User' );
			}

			return pht( 'WikiForge User' );
		}
	}

	public function getFieldValue() {
		$account = $this->getExternalAccount();

		if ( !$account || !strlen( $account->getAccountURI() ) ) {
			return null;
		}

		$uri = urldecode( $account->getAccountURI() );

		// Split on the User: part of the userpage uri
		$name = explode( 'User:', $uri );
		// grab the part after User:
		$name = array_pop( $name );
		// decode for display:
		$name = urldecode( rawurldecode( $name ) );

		return $name;
	}

	protected function getExternalAccount() {
		if ( !$this->externalAccount ) {
			$user = $this->getObject();
			$this->externalAccount = id( new PhabricatorExternalAccount() )->loadOneWhere(
				'userPHID = %s AND accountType = %s',
				$user->getPHID(),
				'mediawiki'
			);
		}

		return $this->externalAccount;
	}

	public function shouldAppearInPropertyView() {
		return true;
	}

	public function renderPropertyViewLabel() {
		return $this->getFieldName();
	}

	public function renderPropertyViewValue( array $handles ) {
		$account = $this->getExternalAccount();

		if ( $account === null ) {
			return;
		}

		$uri = $account->getAccountURI();

		if ( !$account || !strlen( $uri ) ) {
			return pht( 'Unknown' );
		} else {
			$userpage_uri = urldecode( $uri );
		}

		// Split on the User: part of the userpage uri
		$name = explode( 'User:', $userpage_uri );

		// grab the part after User:
		$rawname = array_pop( $name );
		// decode for display:
		$name = urldecode( rawurldecode( $rawname ) );
		$userpage_uri = [ 'href' => $userpage_uri ];

		$global_accounts = [];
		if ( strpos( $uri, 'wikitide.org' ) !== false ) {
			$accounts_uri = [ 'href' =>
					"https://meta.wikitide.org/wiki/Special:CentralAuth/" .
					$rawname ];
			$accounts_text = pht( 'Global Accounts' );
			$global_accounts = [
				' [ ',
				phutil_tag( 'a', $accounts_uri, $accounts_text ),
				' ]'
			];

		}

		return phutil_tag( 'span', [], array_merge( [
			phutil_tag( 'a', $userpage_uri, $name )
		], $global_accounts ) );
	}

	public function shouldAppearInApplicationSearch() {
		return true;
	}

	public function getFieldType() {
		return 'text';
	}

	public function buildFieldIndexes() {
		$indexes = [];

		$value = $this->getFieldValue();
		if ( strlen( $value ) ) {
			$indexes[] = $this->newStringIndex( $value );
			$indexes[] = $this->newStringIndex( urldecode( $this->getExternalAccount()->getAccountURI() ) );
			$parts = explode( ' ', $value );
			if ( count( $parts ) > 1 ) {
				foreach ( $parts as $part ) {
					$indexes[] = $this->newStringIndex( $part );
				}
			}
		}

		return $indexes;
	}

	public function readApplicationSearchValueFromRequest(
		PhabricatorApplicationSearchEngine $engine,
		AphrontRequest $request
	) {
		return $request->getStr( $this->getFieldKey() );
	}

	public function applyApplicationSearchConstraintToQuery(
		PhabricatorApplicationSearchEngine $engine,
		PhabricatorCursorPagedPolicyAwareQuery $query,
		$value
	) {
		if ( strlen( $value ) ) {
			$query->withApplicationSearchContainsConstraint(
				$this->newStringIndex( null ),
				$value
			);
		}
	}

	public function appendToApplicationSearchForm(
		PhabricatorApplicationSearchEngine $engine,
		AphrontFormView $form,
		$value
	) {
		$form->appendChild(
			id( new AphrontFormTextControl() )
				->setLabel( $this->getFieldName() )
				->setName( $this->getFieldKey() )
				->setValue( $value )
		);
	}

	protected function newStringIndexStorage() {
		return new PhabricatorUserCustomFieldStringIndex();
	}
}
