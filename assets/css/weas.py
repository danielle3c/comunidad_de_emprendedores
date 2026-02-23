import pygame
import random

pygame.init()

# Configuración
ANCHO = 300
ALTO = 600
TAMANO_BLOQUE = 30

COLUMNAS = ANCHO // TAMANO_BLOQUE
FILAS = ALTO // TAMANO_BLOQUE

pantalla = pygame.display.set_mode((ANCHO, ALTO))
pygame.display.set_caption("Tetris en Python")

# Colores
NEGRO = (0, 0, 0)
GRIS = (50, 50, 50)
COLORES = [
    (0, 255, 255),
    (0, 0, 255),
    (255, 165, 0),
    (255, 255, 0),
    (0, 255, 0),
    (128, 0, 128),
    (255, 0, 0)
]

# Formas
FORMAS = [
    [[1, 1, 1, 1]],
    [[1, 0, 0], [1, 1, 1]],
    [[0, 0, 1], [1, 1, 1]],
    [[1, 1], [1, 1]],
    [[0, 1, 1], [1, 1, 0]],
    [[0, 1, 0], [1, 1, 1]],
    [[1, 1, 0], [0, 1, 1]]
]

class Pieza:
    def __init__(self):
        self.forma = random.choice(FORMAS)
        self.color = random.choice(COLORES)
        self.x = COLUMNAS // 2 - len(self.forma[0]) // 2
        self.y = 0

def crear_grid(bloques={}):
    grid = [[NEGRO for _ in range(COLUMNAS)] for _ in range(FILAS)]
    for y in range(FILAS):
        for x in range(COLUMNAS):
            if (x, y) in bloques:
                grid[y][x] = bloques[(x, y)]
    return grid

def dibujar_grid(grid):
    for y in range(FILAS):
        for x in range(COLUMNAS):
            pygame.draw.rect(
                pantalla,
                grid[y][x],
                (x*TAMANO_BLOQUE, y*TAMANO_BLOQUE, TAMANO_BLOQUE, TAMANO_BLOQUE)
            )
            pygame.draw.rect(
                pantalla,
                GRIS,
                (x*TAMANO_BLOQUE, y*TAMANO_BLOQUE, TAMANO_BLOQUE, TAMANO_BLOQUE),
                1
            )

def main():
    reloj = pygame.time.Clock()
    bloques = {}
    pieza_actual = Pieza()
    caer_tiempo = 0

    corriendo = True
    while corriendo:
        grid = crear_grid(bloques)
        caer_tiempo += reloj.get_rawtime()
        reloj.tick()

        if caer_tiempo > 500:
            pieza_actual.y += 1
            caer_tiempo = 0

        for evento in pygame.event.get():
            if evento.type == pygame.QUIT:
                corriendo = False

            if evento.type == pygame.KEYDOWN:
                if evento.key == pygame.K_LEFT:
                    pieza_actual.x -= 1
                if evento.key == pygame.K_RIGHT:
                    pieza_actual.x += 1
                if evento.key == pygame.K_DOWN:
                    pieza_actual.y += 1

        for y, fila in enumerate(pieza_actual.forma):
            for x, celda in enumerate(fila):
                if celda:
                    if pieza_actual.y + y >= FILAS:
                        for yy, fila in enumerate(pieza_actual.forma):
                            for xx, celda2 in enumerate(fila):
                                if celda2:
                                    bloques[(pieza_actual.x+xx, pieza_actual.y+yy-1)] = pieza_actual.color
                        pieza_actual = Pieza()
                        break
                    else:
                        grid[pieza_actual.y + y][pieza_actual.x + x] = pieza_actual.color

        pantalla.fill(NEGRO)
        dibujar_grid(grid)
        pygame.display.update()

    pygame.quit()

if __name__ == "__main__":
    main()