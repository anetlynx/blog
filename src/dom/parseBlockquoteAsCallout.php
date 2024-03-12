<?php

function parseBlockquoteAsCallout(DOMElement $domElement): DOMElement {
	$calloutMatches = null;

	if ($domElement->nodeName === 'blockquote' && str_starts_with(trim($domElement->textContent), '[!')) {
		preg_match('/^(?:(\[![\w-]+\][+-]?))/', trim($domElement->textContent), $calloutMatches);

		$calloutPNode = $domElement->firstChild;

		if ($calloutPNode->nodeName !== 'p') {
			throw new Exception('Expected blockquote with callout to have a p as first child, got ' . $calloutPNode->nodeName . ' instead');
		}

		$calloutPTextNode = $calloutPNode->firstChild;

		if ($calloutPTextNode->nodeType !== XML_TEXT_NODE) {
			throw new Exception('Expected blockquote with callout to have a text node as first child of p');
		}

		$calloutPTextContent = explode("\n", $calloutPTextNode->textContent);
		$calloutString = array_shift($calloutPTextContent);
		$calloutString = trim($calloutString);

		$calloutPTextContent = explode("\n", $calloutPTextNode->textContent);
		$calloutString = array_shift($calloutPTextContent);
		$calloutString = trim($calloutString);

		// Update the text node with rest:
		$calloutPTextNode->textContent = trim(implode("\n", $calloutPTextContent));

		// Extract the callout:
		if (preg_match('/^(?:\[!([\w-]+)\]([+-]?)\s*?([^$]*?))$/', $calloutString, $matches)) {
			$matches[3] = trim($matches[3]);
			$matches[3] = $matches[3] === '' ? null : $matches[3];

			// Create summary element:
			$summary = $domElement->ownerDocument->createElement('summary');
			$summary->textContent = $matches[3] ?? ucfirst($matches[1]);
			$summary->setAttribute('class', 'callout-summary');
			$summary->setAttribute('data-type', $matches[1]);

			$svg = $domElement->ownerDocument->createDocumentFragment();
			$svg->appendXML(match($matches[1]) {
				'info' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-info"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>',
				'abstract', 'summary', 'tldr' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-clipboard-list"><rect width="8" height="4" x="8" y="2" rx="1" ry="1"/><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><path d="M12 11h4"/><path d="M12 16h4"/><path d="M8 11h.01"/><path d="M8 16h.01"/></svg>',
				'todo' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-circle-check"><circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/></svg>',
				'tip', 'hint' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-lightbulb"><path d="M15 14c.2-1 .7-1.7 1.5-2.5 1-.9 1.5-2.2 1.5-3.5A6 6 0 0 0 6 8c0 1 .2 2.2 1.5 3.5.7.7 1.3 1.5 1.5 2.5"/><path d="M9 18h6"/><path d="M10 22h4"/></svg>',
				'important' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-message-circle-warning"><path d="M7.9 20A9 9 0 1 0 4 16.1L2 22Z"/><path d="M12 8v4"/><path d="M12 16h.01"/></svg>',
				'success', 'check', 'done' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-check-check"><path d="M18 6 7 17l-5-5"/><path d="m22 10-7.5 7.5L13 16"/></svg>',
				'question', 'help', 'faq' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-message-circle-question"><path d="M7.9 20A9 9 0 1 0 4 16.1L2 22Z"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><path d="M12 17h.01"/></svg>',
				'warning', 'caution', 'attention' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-triangle-alert"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3"/><path d="M12 9v4"/><path d="M12 17h.01"/></svg>',
				'failure', 'fail', 'missing', 'error' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-circle-x"><circle cx="12" cy="12" r="10"/><path d="m15 9-6 6"/><path d="m9 9 6 6"/></svg>',
				'danger' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-zap"><path d="M4 14a1 1 0 0 1-.78-1.63l9.9-10.2a.5.5 0 0 1 .86.46l-1.92 6.02A1 1 0 0 0 13 10h7a1 1 0 0 1 .78 1.63l-9.9 10.2a.5.5 0 0 1-.86-.46l1.92-6.02A1 1 0 0 0 11 14z"/></svg>',
				'bug' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-bug"><path d="m8 2 1.88 1.88"/><path d="M14.12 3.88 16 2"/><path d="M9 7.13v-1a3.003 3.003 0 1 1 6 0v1"/><path d="M12 20c-3.3 0-6-2.7-6-6v-3a4 4 0 0 1 4-4h4a4 4 0 0 1 4 4v3c0 3.3-2.7 6-6 6"/><path d="M12 20v-9"/><path d="M6.53 9C4.6 8.8 3 7.1 3 5"/><path d="M6 13H2"/><path d="M3 21c0-2.1 1.7-3.9 3.8-4"/><path d="M20.97 5c0 2.1-1.6 3.8-3.5 4"/><path d="M22 13h-4"/><path d="M17.2 17c2.1.1 3.8 1.9 3.8 4"/></svg>',
				'example' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-list"><line x1="8" x2="21" y1="6" y2="6"/><line x1="8" x2="21" y1="12" y2="12"/><line x1="8" x2="21" y1="18" y2="18"/><line x1="3" x2="3.01" y1="6" y2="6"/><line x1="3" x2="3.01" y1="12" y2="12"/><line x1="3" x2="3.01" y1="18" y2="18"/></svg>',
				default => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-pencil-line"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/><path d="m15 5 3 3"/></svg>',
			});
			$summary->prepend($svg);

			// Create details element:
			$details = $domElement->ownerDocument->createElement('details');
			$details->setAttribute('class', 'callout');
			$details->setAttribute('data-type', $matches[1]);

			if ($matches[2] !== '-') {
				$details->setAttribute('open', 'true');
			}

			if (empty($matches[2])) {
				$details->setAttribute('data-toggle', 'false');
			}

			// Append summary to details:
			$details->appendChild($summary);

			// Append all children of blockquote to details:
			foreach ($domElement->childNodes as $childNode) {
				if ($childNode instanceof DOMElement) {
					$details->appendChild(parseBlockquoteAsCallout($childNode));
				} else {
					$details->appendChild($childNode->cloneNode(true));
				}
			}

			return $details;
		} else {
			throw new Exception('Expected blockquote with callout to have a valid callout');
		}
	}

	$newChildren = [];

	foreach ($domElement->childNodes as $childNode) {
		if ($childNode instanceof DOMElement) {
			$newChildren[] = parseBlockquoteAsCallout($childNode);
		} else {
			$newChildren[] = $childNode->cloneNode(true);
		}
	}

	$element = $domElement->cloneNode();

	while ($element->hasChildNodes()) {
		$element->removeChild($element->firstChild);
	}

	foreach ($newChildren as $newChild) {
		$element->appendChild($newChild);
	}

	return $element;
}
